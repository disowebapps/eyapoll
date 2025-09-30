<?php

namespace App\Services;

use App\Models\Alert;
use App\Events\AdminAlert;

class AlertService
{
    public function createAlert($type, $title, $message, $priority = 'medium')
    {
        $alert = Alert::create([
            'type' => $type,
            'priority' => $priority,
            'title' => $title,
            'message' => $message,
            'is_read' => false,
        ]);

        // Fire event for any listeners (no broadcasting)
        event(new AdminAlert($alert));

        return $alert;
    }

    public function kycSubmission($userName, $documentType)
    {
        return $this->createAlert(
            'kyc_submission',
            'New KYC Submission',
            "User {$userName} submitted {$documentType} for verification",
            'high'
        );
    }

    public function candidateApplication($candidateName, $position)
    {
        return $this->createAlert(
            'candidate_application',
            'New Candidate Application',
            "{$candidateName} applied for {$position}",
            'high'
        );
    }

    public function securityAlert($message)
    {
        return $this->createAlert(
            'security_alert',
            'Security Alert',
            $message,
            'critical'
        );
    }

    public function electoralIntegrity($message)
    {
        return $this->createAlert(
            'electoral_integrity',
            'Electoral Integrity Issue',
            $message,
            'critical'
        );
    }

    public function observerAlert($observerName, $message)
    {
        return $this->createAlert(
            'observer_alert',
            'Observer Alert',
            "Observer {$observerName}: {$message}",
            'medium'
        );
    }

    public function systemNotification($message)
    {
        return $this->createAlert(
            'system_notification',
            'System Notification',
            $message,
            'low'
        );
    }
}