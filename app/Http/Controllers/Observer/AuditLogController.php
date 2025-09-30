<?php

namespace App\Http\Controllers\Observer;

use App\Http\Controllers\Controller;
use App\Models\Audit\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function export(Request $request)
    {
        // Authorize the export action
        Gate::authorize('exportAuditLogs', auth('observer')->user());

        // Build the query with the same filters as the Livewire component
        $query = AuditLog::query()
            ->with(['user', 'entity'])
            ->when($request->search, function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('action', 'like', $searchTerm)
                      ->orWhere('description', 'like', $searchTerm)
                      ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                          $userQuery->where('first_name', 'like', $searchTerm)
                                    ->orWhere('last_name', 'like', $searchTerm)
                                    ->orWhere('email', 'like', $searchTerm);
                      });
                });
            })
            ->when($request->action, function ($query) use ($request) {
                $query->where('action', $request->action);
            })
            ->when($request->user, function ($query) use ($request) {
                $query->where('user_id', $request->user);
            })
            ->when($request->entity, function ($query) use ($request) {
                $query->where('entity_type', $request->entity);
            })
            ->when($request->date_from, function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->date_to, function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_to);
            })
            ->orderBy('created_at', 'desc');

        $format = $request->get('format', 'csv');

        // Log the export action
        Log::info('Audit logs exported', [
            'observer_id' => auth('observer')->id(),
            'format' => $format,
            'record_count' => $query->count(),
            'filters' => $request->all(),
            'ip_address' => $request->ip(),
        ]);

        switch ($format) {
            case 'excel':
            case 'xlsx':
                return $this->exportExcel($query, $request);
            case 'json':
                return $this->exportJson($query, $request);
            case 'csv':
            default:
                return $this->exportCsv($query, $request);
        }
    }

    private function exportCsv($query, Request $request): StreamedResponse
    {
        $filename = 'audit-logs-' . now()->format('Y-m-d-H-i-s') . '.csv';

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // Write CSV header
            fputcsv($handle, [
                'ID',
                'Timestamp',
                'User',
                'User Type',
                'Action',
                'Entity Type',
                'Entity ID',
                'Description',
                'IP Address',
                'User Agent',
                'Integrity Verified'
            ]);

            // Write data in chunks to handle large datasets
            $query->chunk(1000, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    fputcsv($handle, [
                        $log->id,
                        $log->created_at->format('Y-m-d H:i:s'),
                        $log->getUserName(),
                        $log->user_type ?? 'system',
                        $log->getActionLabel(),
                        class_basename($log->entity_type ?? ''),
                        $log->entity_id ?? '',
                        $log->description ?? '',
                        $log->ip_address ?? '',
                        substr($log->user_agent ?? '', 0, 100), // Truncate user agent
                        $log->verifyIntegrity() ? 'Yes' : 'No'
                    ]);
                }
            });

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    private function exportExcel($query, Request $request): StreamedResponse
    {
        // For Excel export, we'll use CSV format with Excel-compatible headers
        $filename = 'audit-logs-' . now()->format('Y-m-d-H-i-s') . '.csv';

        // Note: In a production environment, you would use a proper Excel library like PhpSpreadsheet
        // For now, we'll export as CSV that Excel can open

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // Write CSV header
            fputcsv($handle, [
                'ID',
                'Timestamp',
                'User',
                'User Type',
                'Action',
                'Entity Type',
                'Entity ID',
                'Description',
                'IP Address',
                'User Agent',
                'Integrity Verified'
            ]);

            // Write data in chunks
            $query->chunk(1000, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    fputcsv($handle, [
                        $log->id,
                        $log->created_at->format('Y-m-d H:i:s'),
                        $log->getUserName(),
                        $log->user_type ?? 'system',
                        $log->getActionLabel(),
                        class_basename($log->entity_type ?? ''),
                        $log->entity_id ?? '',
                        $log->description ?? '',
                        $log->ip_address ?? '',
                        substr($log->user_agent ?? '', 0, 100),
                        $log->verifyIntegrity() ? 'Yes' : 'No'
                    ]);
                }
            });

            fclose($handle);
        }, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    private function exportJson($query, Request $request): StreamedResponse
    {
        $filename = 'audit-logs-' . now()->format('Y-m-d-H-i-s') . '.json';

        return response()->stream(function () use ($query) {
            $data = [
                'export_info' => [
                    'timestamp' => now()->toISOString(),
                    'observer' => auth('observer')->user()->full_name ?? 'Unknown',
                    'total_records' => $query->count(),
                    'filters_applied' => request()->all(),
                ],
                'audit_logs' => []
            ];

            // Write data in chunks
            $query->chunk(1000, function ($logs) use (&$data) {
                foreach ($logs as $log) {
                    $data['audit_logs'][] = [
                        'id' => $log->id,
                        'timestamp' => $log->created_at->toISOString(),
                        'user' => $log->getUserName(),
                        'user_type' => $log->user_type ?? 'system',
                        'action' => $log->action,
                        'action_label' => $log->getActionLabel(),
                        'entity_type' => $log->entity_type,
                        'entity_id' => $log->entity_id,
                        'description' => $log->description,
                        'ip_address' => $log->ip_address,
                        'user_agent' => $log->user_agent,
                        'old_values' => $log->old_values,
                        'new_values' => $log->new_values,
                        'integrity_verified' => $log->verifyIntegrity(),
                        'integrity_hash' => $log->integrity_hash,
                    ];
                }
            });

            echo json_encode($data, JSON_PRETTY_PRINT);
        }, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}