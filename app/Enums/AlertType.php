<?php

namespace App\Enums;

enum AlertType: string
{
    case KYC_SUBMISSION = 'kyc_submission';
    case CANDIDATE_APPLICATION = 'candidate_application';
    case SECURITY_ALERT = 'security_alert';
    case INTEGRITY_VIOLATION = 'integrity_violation';
    case OBSERVER_ALERT = 'observer_alert';
    case SYSTEM_ALERT = 'system_alert';
}