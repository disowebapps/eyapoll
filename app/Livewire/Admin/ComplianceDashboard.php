<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\Auth\IdDocument;
use App\Models\System\ComplianceReport;
use Illuminate\Support\Facades\Cache;

class ComplianceDashboard extends Component
{
    public function getComplianceMetricsProperty()
    {
        return Cache::remember('compliance_metrics', 300, function() {
            return [
                'kyc_completion_rate' => $this->getKycCompletionRate(),
                'document_approval_rate' => $this->getDocumentApprovalRate(),
                'average_review_time' => $this->getAverageReviewTime(),
                'compliance_violations' => $this->getComplianceViolations(),
                'audit_trail_completeness' => $this->getAuditTrailCompleteness(),
                'data_retention_compliance' => $this->getDataRetentionCompliance()
            ];
        });
    }

    private function getKycCompletionRate()
    {
        $total = User::count();
        if ($total === 0) return 100;
        
        $completed = User::whereHas('idDocuments', function($q) {
            $q->where('status', 'approved');
        })->count();
        
        return round(($completed / $total) * 100, 1);
    }

    private function getDocumentApprovalRate()
    {
        $total = IdDocument::count();
        if ($total === 0) return 100;
        
        $approved = IdDocument::where('status', 'approved')->count();
        return round(($approved / $total) * 100, 1);
    }

    private function getAverageReviewTime()
    {
        return IdDocument::whereNotNull('reviewed_at')
            ->whereNotNull('created_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, reviewed_at)) as avg_hours')
            ->value('avg_hours') ?? 0;
    }

    private function getComplianceViolations()
    {
        return ComplianceReport::where('status', 'violation')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
    }

    private function getAuditTrailCompleteness()
    {
        // Check if all critical actions have audit logs
        return 98.5; // Mock - implement based on your audit requirements
    }

    private function getDataRetentionCompliance()
    {
        // Check data retention policies
        return 100; // Mock - implement based on your retention policies
    }

    public function render()
    {
        return view('livewire.admin.compliance-dashboard');
    }
}