<?php

namespace App\Http\Controllers\Observer;

use App\Http\Controllers\Controller;
use App\Models\Election\Election;
use App\Models\Audit\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ElectionResultsController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('observer.auth');
        $this->middleware('throttle:60,1')->only('export');
    }

    public function export(Request $request, Election $election, string $format = 'csv')
    {
        Log::info('Export request started', [
            'election_id' => $election->id,
            'format' => $format,
            'request_format' => $request->input('format')
        ]);

        // Validate format parameter
        $request->validate([
            'format' => 'sometimes|in:csv,excel'
        ]);
        
        $format = $request->input('format', $format);
        
        // Security: Validate format against whitelist
        if (!in_array($format, ['csv', 'excel'], true)) {
            Log::error('Invalid export format', ['format' => $format]);
            abort(422, 'Invalid export format specified');
        }

        // Get authenticated observer
        $observer = Auth::guard('observer')->user();
        Log::info('Observer authentication', [
            'observer_id' => $observer?->id,
            'observer_class' => get_class($observer),
            'is_active' => $observer?->isActive(),
            'has_privilege' => $observer?->hasPrivilege('view_election_results')
        ]);
        
        // Authorization check - observers can export if they can view results
        if (!$observer || !$observer->isActive() || !$observer->hasPrivilege('view_election_results')) {
            Log::error('Authorization failed for export', [
                'observer_id' => $observer?->id,
                'is_active' => $observer?->isActive(),
                'has_privilege' => $observer?->hasPrivilege('view_election_results')
            ]);
            abort(403, 'Unauthorized to export election results');
        }
        
        // Security: Verify election exists and observer has access
        if (!$election->exists) {
            abort(404, 'Election not found');
        }

        // Allow export even during ongoing elections for transparency
        // Observers should be able to export current standings
        Log::info('Election validation passed', [
            'election_id' => $election->id,
            'election_title' => $election->title,
            'election_status' => $election->status->value,
            'vote_tokens_count' => $election->voteTokens()->count(),
            'total_votes_cast' => $this->getTotalVotesCast($election)
        ]);

        // Security: Sanitize filename components
        $safeTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $election->title);
        $safeTitle = substr($safeTitle, 0, 50); // Limit length
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "election_{$election->id}_results_{$safeTitle}_{$timestamp}";
        
        Log::info('Filename generated', ['filename' => $filename]);

        // Determine correct user type based on the authenticated user's class
        $userType = match(get_class($observer)) {
            'App\Models\Observer' => 'observer',
            'App\Models\Admin' => 'admin',
            default => 'observer'
        };
        
        // Audit logging with enhanced security context
        AuditLog::create([
            'user_id' => $observer->id,
            'user_type' => $userType,
            'action' => 'export_results',
            'entity_type' => 'App\Models\Election\Election',
            'entity_id' => $election->id,
            'description' => "Exported election results in {$format} format",
            'ip_address' => $request->ip(),
            'user_agent' => substr($request->userAgent(), 0, 500), // Limit length
            'integrity_hash' => hash('sha256', json_encode([
                'observer_id' => $observer->id,
                'election_id' => $election->id,
                'action' => 'export_results',
                'timestamp' => now()->toISOString()
            ])),
            'metadata' => [
                'format' => $format,
                'election_id' => $election->id,
                'observer_id' => $observer->id,
                'export_timestamp' => now()->toISOString(),
                'file_size_estimate' => $this->estimateFileSize($election),
            ],
        ]);

        // Route to appropriate export method
        Log::info('Routing to export method', ['format' => $format]);
        
        try {
            return match($format) {
                'csv' => $this->exportCsv($election, $filename),
                'excel' => $this->exportExcel($election, $filename),
                default => abort(422, 'Unsupported export format')
            };
        } catch (\Exception $e) {
            Log::error('Export failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function exportCsv(Election $election, string $filename): StreamedResponse
    {
        Log::info('Starting CSV export', ['election_id' => $election->id, 'filename' => $filename]);
        
        return response()->stream(function () use ($election) {
            Log::info('CSV stream started');
            $handle = fopen('php://output', 'w');
            
            if (!$handle) {
                Log::error('Failed to open output stream');
                throw new \RuntimeException('Unable to open output stream');
            }

            try {
                // Write BOM for UTF-8
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

                // Election header with sanitized data
                fputcsv($handle, ['Election Results Export']);
                fputcsv($handle, ['Election ID', $election->id]);
                fputcsv($handle, ['Election Title', $this->sanitizeCsvValue($election->title)]);
                fputcsv($handle, ['Election Type', $election->type?->label() ?? 'Unknown']);
                fputcsv($handle, ['Export Date', now()->format('Y-m-d H:i:s')]);
                fputcsv($handle, ['Exported By', $this->sanitizeCsvValue(Auth::guard('observer')->user()->full_name ?? 'System')]);
                fputcsv($handle, []);

                // Performance: Use efficient counting
                $voteTokensCount = $election->voteTokens()->count();
                $votesCount = $this->getTotalVotesCast($election);
                $positionsCount = $election->positions()->count();

                // Overall statistics
                fputcsv($handle, ['Overall Statistics']);
                fputcsv($handle, ['Eligible Voters', $voteTokensCount]);
                fputcsv($handle, ['Total Votes Cast', $this->getTotalVotesCast($election)]);
                fputcsv($handle, ['Voter Turnout %', $voteTokensCount > 0 ?
                    round(($this->getTotalVotesCast($election) / $voteTokensCount) * 100, 2) : 0]);
                fputcsv($handle, ['Positions', $positionsCount]);
                fputcsv($handle, ['Election Status', ucfirst($election->status->value)]);
                fputcsv($handle, []);

                // Performance: Load positions with relationships in chunks
                $election->positions()
                    ->with(['candidates.user'])
                    ->orderBy('order_index')
                    ->chunk(10, function ($positions) use ($handle) {
                        foreach ($positions as $position) {
                            $this->writePositionResults($handle, $position);
                        }
                    });

            } finally {
                if (is_resource($handle)) {
                    fclose($handle);
                }
            }
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
        ]);
    }

    /**
     * Write position results to CSV handle
     */
    private function writePositionResults($handle, $position): void
    {
        fputcsv($handle, ["Position: {$this->sanitizeCsvValue($position->title)}"]);
        fputcsv($handle, ['Position Description', $this->sanitizeCsvValue($position->description)]);
        
        // Get vote tallies for this position
        $results = DB::table('vote_tallies')
            ->join('candidates', 'vote_tallies.candidate_id', '=', 'candidates.id')
            ->join('users', 'candidates.user_id', '=', 'users.id')
            ->select(
                'vote_tallies.candidate_id',
                DB::raw('CONCAT(users.first_name, " ", users.last_name) as candidate_name'),
                'vote_tallies.vote_count'
            )
            ->where('vote_tallies.position_id', $position->id)
            ->orderBy('vote_tallies.vote_count', 'desc')
            ->get();
        
        $totalVotes = $results->sum('vote_count');
        fputcsv($handle, ['Total Votes', $totalVotes]);
        fputcsv($handle, ['Approved Candidates', $position->candidates()->where('status', 'approved')->count()]);
        fputcsv($handle, []);

        // Results header
        fputcsv($handle, ['Candidate', 'Votes', 'Percentage', 'Ranking', 'Status']);

        foreach ($results as $index => $result) {
            $percentage = $totalVotes > 0 ? round(($result->vote_count / $totalVotes) * 100, 2) : 0;
            $ranking = $index + 1;
            $status = $ranking === 1 && $totalVotes > 0 ? 'Leading' : '-';

            fputcsv($handle, [
                $this->sanitizeCsvValue($result->candidate_name),
                (int) $result->vote_count,
                number_format($percentage, 2) . '%',
                '#' . $ranking,
                $status
            ]);
        }

        fputcsv($handle, []);
    }

    /**
     * Sanitize CSV values to prevent injection attacks
     */
    private function sanitizeCsvValue(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        
        // Remove potential CSV injection characters
        $value = str_replace(['=', '+', '-', '@'], '', $value);
        
        // Limit length and remove control characters
        $value = substr($value, 0, 1000);
        $value = preg_replace('/[\x00-\x1F\x7F]/', '', $value);
        
        return trim($value);
    }

    private function exportExcel(Election $election, string $filename): StreamedResponse
    {
        // For now, use CSV format - can be enhanced with Laravel Excel package later
        return $this->exportCsv($election, $filename);
    }

    /**
     * Estimate file size for audit logging
     */
    private function estimateFileSize(Election $election): string
    {
        $positionCount = $election->positions()->count();
        $voteCount = $this->getTotalVotesCast($election);
        $candidateCount = $election->positions()->withCount('candidates')->get()->sum('candidates_count');
        
        // Rough estimation: headers + data rows
        $estimatedBytes = ($positionCount * 500) + ($candidateCount * 100) + ($voteCount * 50);
        
        return $this->formatBytes($estimatedBytes);
    }

    /**
     * Get total votes cast using vote_tallies table
     */
    private function getTotalVotesCast(Election $election): int
    {
        $totalVotes = DB::table('vote_tallies')
            ->join('positions', 'vote_tallies.position_id', '=', 'positions.id')
            ->where('positions.election_id', $election->id)
            ->whereNotNull('vote_tallies.candidate_id')
            ->sum('vote_tallies.vote_count');
            
        Log::info('Total votes calculated', [
            'election_id' => $election->id,
            'total_votes' => $totalVotes
        ]);
        
        return $totalVotes;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
