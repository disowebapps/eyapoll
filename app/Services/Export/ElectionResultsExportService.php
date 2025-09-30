<?php

namespace App\Services\Export;

use App\Models\Election\Election;
use App\Models\Voting\VoteTally;
use App\Services\Security\FilePathValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ElectionResultsExportService
{
    /**
     * Export election results as CSV
     */
    public static function exportCsv(Election $election, string $filename): StreamedResponse
    {
        return Response::stream(function () use ($election) {
            $handle = fopen('php://output', 'w');

            // Write CSV headers
            fputcsv($handle, [
                'Position',
                'Candidate',
                'Votes',
                'Percentage',
                'Ranking',
                'Status',
                'Last Updated'
            ]);

            // Get results for each position
            foreach ($election->positions()->orderBy(DB::raw('`order_index`'))->get() as $position) {
                $tallies = $position->voteTallies()
                    ->with('candidate.user')
                    ->orderBy(DB::raw('`vote_count` DESC'))
                    ->get();

                $totalVotes = $tallies->sum('vote_count');

                foreach ($tallies as $tally) {
                    fputcsv($handle, [
                        $position->title,
                        $tally->getCandidateName(),
                        $tally->vote_count,
                        $totalVotes > 0 ? round(($tally->vote_count / $totalVotes) * 100, 2) . '%' : '0%',
                        $tally->getRanking(),
                        $tally->isWinning() ? 'Winner' : 'Not Winner',
                        $tally->last_updated?->format('Y-m-d H:i:s') ?? 'N/A'
                    ]);
                }

                // Add separator row
                fputcsv($handle, ['', '', '', '', '', '', '']);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . self::secureFilename($filename) . '.csv"',
        ]);
    }

    /**
     * Export election results as Excel (simplified CSV for now)
     */
    public static function exportExcel(Election $election, string $filename): StreamedResponse
    {
        // For now, return CSV with Excel headers
        // In production, you might want to use Laravel Excel or similar
        return Response::stream(function () use ($election) {
            $handle = fopen('php://output', 'w');

            // Write Excel-compatible headers
            fputcsv($handle, [
                'Election',
                'Type',
                'Status',
                'Total Eligible Voters',
                'Total Votes Cast',
                'Turnout %'
            ]);

            fputcsv($handle, [
                $election->title,
                $election->type->label(),
                $election->status->label(),
                $election->voteTokens->count(),
                $election->votes->count(),
                $election->voteTokens->count() > 0 ?
                    round(($election->votes->count() / $election->voteTokens->count()) * 100, 2) . '%' : '0%'
            ]);

            // Empty row
            fputcsv($handle, ['', '', '', '', '', '']);

            // Position results
            fputcsv($handle, ['Position Results']);
            fputcsv($handle, ['Position', 'Candidate', 'Votes', 'Percentage', 'Ranking', 'Winner']);

            foreach ($election->positions()->orderBy(DB::raw('`order_index`'))->get() as $position) {
                $tallies = $position->voteTallies()
                    ->with('candidate.user')
                    ->orderBy(DB::raw('`vote_count` DESC'))
                    ->get();

                $totalVotes = $tallies->sum('vote_count');

                foreach ($tallies as $tally) {
                    fputcsv($handle, [
                        $position->title,
                        $tally->getCandidateName(),
                        $tally->vote_count,
                        $totalVotes > 0 ? round(($tally->vote_count / $totalVotes) * 100, 2) . '%' : '0%',
                        $tally->getRanking(),
                        $tally->isWinning() ? 'Yes' : 'No'
                    ]);
                }

                fputcsv($handle, ['', '', '', '', '', '']);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . self::secureFilename($filename) . '.xls"',
        ]);
    }

    /**
     * Export audit trail for election results
     */
    public static function exportAudit(Election $election, string $filename): StreamedResponse
    {
        return Response::stream(function () use ($election) {
            $handle = fopen('php://output', 'w');

            // Write audit headers
            fputcsv($handle, [
                'Timestamp',
                'Action',
                'User',
                'User Type',
                'Entity Type',
                'Entity ID',
                'IP Address',
                'Details'
            ]);

            // Get audit logs for this election
            $auditLogs = \App\Models\Audit\AuditLog::where(function ($query) use ($election) {
                $query->where('entity_type', '=', 'App\Models\Election\Election')
                      ->where('entity_id', '=', $election->id)
                      ->orWhere('action', 'like', '%election%')
                      ->orWhere('action', 'like', '%vote%');
            })
            ->orderBy(DB::raw('`created_at` DESC'))
            ->get();

            foreach ($auditLogs as $log) {
                fputcsv($handle, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->getActionLabel(),
                    $log->getUserName(),
                    $log->user_type ?? 'system',
                    $log->entity_type,
                    $log->entity_id,
                    $log->ip_address,
                    json_encode($log->getChangeSummary())
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . self::secureFilename($filename) . '_audit.csv"',
        ]);
    }

    private static function secureFilename(string $filename): string
    {
        // Remove path traversal attempts and dangerous characters
        $filename = basename($filename);
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
        $filename = trim($filename, '._');
        
        // Ensure filename is not empty and has reasonable length
        if (empty($filename) || strlen($filename) > 100) {
            $filename = 'export_' . date('Y_m_d_H_i_s');
        }
        
        return $filename;
    }
}
