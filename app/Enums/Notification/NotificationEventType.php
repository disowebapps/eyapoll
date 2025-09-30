<?php

namespace App\Enums\Notification;

enum NotificationEventType: string
{
    // User Events
    case USER_REGISTERED = 'user_registered';
    case USER_APPROVED = 'user_approved';
    case USER_REJECTED = 'user_rejected';
    case USER_SUSPENDED = 'user_suspended';
    case USER_PASSWORD_RESET = 'user_password_reset';

    // Candidate Events
    case CANDIDATE_APPLIED = 'candidate_applied';
    case CANDIDATE_APPROVED = 'candidate_approved';
    case CANDIDATE_REJECTED = 'candidate_rejected';
    case CANDIDATE_DOCUMENTS_REQUESTED = 'candidate_documents_requested';

    // Election Events
    case ELECTION_CREATED = 'election_created';
    case ELECTION_STARTED = 'election_started';
    case ELECTION_ENDED = 'election_ended';
    case ELECTION_CANCELLED = 'election_cancelled';
    case ELECTION_RESULTS_AVAILABLE = 'election_results_available';

    // Voting Events
    case VOTE_CAST = 'vote_cast';
    case VOTE_VERIFIED = 'vote_verified';
    case VOTE_RECEIPT_REQUESTED = 'vote_receipt_requested';
    case VOTE_TOKEN_GENERATED = 'vote_token_generated';

    // Security Events
    case FAILED_LOGIN_ATTEMPT = 'failed_login_attempt';
    case SUSPICIOUS_ACTIVITY = 'suspicious_activity';
    case ADMIN_LOGIN = 'admin_login';
    case PASSWORD_CHANGED = 'password_changed';

    // System Events
    case SYSTEM_MAINTENANCE = 'system_maintenance';
    case SYSTEM_BACKUP_COMPLETED = 'system_backup_completed';
    case SYSTEM_ERROR = 'system_error';

    // Communication Events
    case NEWSLETTER_SUBSCRIPTION = 'newsletter_subscription';
    case CONTACT_FORM_SUBMITTED = 'contact_form_submitted';
    case SUPPORT_REQUEST = 'support_request';

    // Appeal Events
    case APPEAL_SUBMITTED = 'appeal_submitted';
    case APPEAL_UNDER_REVIEW = 'appeal_under_review';
    case APPEAL_APPROVED = 'appeal_approved';
    case APPEAL_REJECTED = 'appeal_rejected';
    case APPEAL_DISMISSED = 'appeal_dismissed';
    case APPEAL_ASSIGNED = 'appeal_assigned';
    case APPEAL_ESCALATED = 'appeal_escalated';
    case APPEALS_OVERDUE = 'appeals_overdue';
    case APPEALS_NEED_ESCALATION = 'appeals_need_escalation';
    case APPEAL_DEADLINE_REMINDER = 'appeal_deadline_reminder';
    case APPEAL_DOCUMENT_APPROVED = 'appeal_document_approved';
    case APPEAL_DOCUMENT_REJECTED = 'appeal_document_rejected';

    // Document Events
    case DOCUMENT_EXPIRING = 'document_expiring';
    case DOCUMENT_EXPIRED = 'document_expired';

    public function label(): string
    {
        return match($this) {
            self::USER_REGISTERED => 'User Registered',
            self::USER_APPROVED => 'User Approved',
            self::USER_REJECTED => 'User Rejected',
            self::USER_SUSPENDED => 'User Suspended',
            self::USER_PASSWORD_RESET => 'Password Reset',

            self::CANDIDATE_APPLIED => 'Candidate Applied',
            self::CANDIDATE_APPROVED => 'Candidate Approved',
            self::CANDIDATE_REJECTED => 'Candidate Rejected',
            self::CANDIDATE_DOCUMENTS_REQUESTED => 'Documents Requested',

            self::ELECTION_CREATED => 'Election Created',
            self::ELECTION_STARTED => 'Election Started',
            self::ELECTION_ENDED => 'Election Ended',
            self::ELECTION_CANCELLED => 'Election Cancelled',
            self::ELECTION_RESULTS_AVAILABLE => 'Results Available',

            self::VOTE_CAST => 'Vote Cast',
            self::VOTE_VERIFIED => 'Vote Verified',
            self::VOTE_RECEIPT_REQUESTED => 'Receipt Requested',
            self::VOTE_TOKEN_GENERATED => 'Vote Token Generated',

            self::FAILED_LOGIN_ATTEMPT => 'Failed Login',
            self::SUSPICIOUS_ACTIVITY => 'Suspicious Activity',
            self::ADMIN_LOGIN => 'Admin Login',
            self::PASSWORD_CHANGED => 'Password Changed',

            self::SYSTEM_MAINTENANCE => 'System Maintenance',
            self::SYSTEM_BACKUP_COMPLETED => 'Backup Completed',
            self::SYSTEM_ERROR => 'System Error',

            self::NEWSLETTER_SUBSCRIPTION => 'Newsletter Subscription',
            self::CONTACT_FORM_SUBMITTED => 'Contact Form',
            self::SUPPORT_REQUEST => 'Support Request',

            self::APPEAL_SUBMITTED => 'Appeal Submitted',
            self::APPEAL_UNDER_REVIEW => 'Appeal Under Review',
            self::APPEAL_APPROVED => 'Appeal Approved',
            self::APPEAL_REJECTED => 'Appeal Rejected',
            self::APPEAL_DISMISSED => 'Appeal Dismissed',
            self::APPEAL_ASSIGNED => 'Appeal Assigned',
            self::APPEAL_ESCALATED => 'Appeal Escalated',
            self::APPEALS_OVERDUE => 'Appeals Overdue',
            self::APPEALS_NEED_ESCALATION => 'Appeals Need Escalation',
            self::APPEAL_DEADLINE_REMINDER => 'Appeal Deadline Reminder',
            self::APPEAL_DOCUMENT_APPROVED => 'Appeal Document Approved',
            self::APPEAL_DOCUMENT_REJECTED => 'Appeal Document Rejected',

            self::DOCUMENT_EXPIRING => 'Document Expiring Soon',
            self::DOCUMENT_EXPIRED => 'Document Expired',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::USER_REGISTERED => 'Sent when a new user registers on the platform',
            self::USER_APPROVED => 'Sent when a user account is approved by admin',
            self::USER_REJECTED => 'Sent when a user account is rejected by admin',
            self::USER_SUSPENDED => 'Sent when a user account is suspended',
            self::USER_PASSWORD_RESET => 'Sent when a user requests password reset',

            self::CANDIDATE_APPLIED => 'Sent when a candidate submits an application',
            self::CANDIDATE_APPROVED => 'Sent when a candidate application is approved',
            self::CANDIDATE_REJECTED => 'Sent when a candidate application is rejected',
            self::CANDIDATE_DOCUMENTS_REQUESTED => 'Sent when additional documents are requested',

            self::ELECTION_CREATED => 'Sent when a new election is created',
            self::ELECTION_STARTED => 'Sent when an election begins accepting votes',
            self::ELECTION_ENDED => 'Sent when an election ends',
            self::ELECTION_CANCELLED => 'Sent when an election is cancelled',
            self::ELECTION_RESULTS_AVAILABLE => 'Sent when election results are published',

            self::VOTE_CAST => 'Sent when a user successfully casts a vote',
            self::VOTE_VERIFIED => 'Sent when a vote receipt is verified',
            self::VOTE_RECEIPT_REQUESTED => 'Sent when a user requests vote verification',
            self::VOTE_TOKEN_GENERATED => 'Sent when voting tokens are generated for eligible voters',

            self::FAILED_LOGIN_ATTEMPT => 'Sent when login attempts fail repeatedly',
            self::SUSPICIOUS_ACTIVITY => 'Sent when suspicious activity is detected',
            self::ADMIN_LOGIN => 'Sent when an admin logs into the system',
            self::PASSWORD_CHANGED => 'Sent when a user changes their password',

            self::SYSTEM_MAINTENANCE => 'Sent for scheduled maintenance notifications',
            self::SYSTEM_BACKUP_COMPLETED => 'Sent when system backups complete',
            self::SYSTEM_ERROR => 'Sent when critical system errors occur',

            self::NEWSLETTER_SUBSCRIPTION => 'Sent when users subscribe to newsletters',
            self::CONTACT_FORM_SUBMITTED => 'Sent when contact forms are submitted',
            self::SUPPORT_REQUEST => 'Sent when users request support',

            self::APPEAL_SUBMITTED => 'Sent when a new appeal is submitted',
            self::APPEAL_UNDER_REVIEW => 'Sent when an appeal status changes to under review',
            self::APPEAL_APPROVED => 'Sent when an appeal is approved',
            self::APPEAL_REJECTED => 'Sent when an appeal is rejected',
            self::APPEAL_DISMISSED => 'Sent when an appeal is dismissed',
            self::APPEAL_ASSIGNED => 'Sent when an appeal is assigned to an admin',
            self::APPEAL_ESCALATED => 'Sent when an appeal priority is escalated',
            self::APPEALS_OVERDUE => 'Sent when appeals exceed their deadlines',
            self::APPEALS_NEED_ESCALATION => 'Sent when appeals need priority escalation',
            self::APPEAL_DEADLINE_REMINDER => 'Sent as reminder before appeal deadline',
            self::APPEAL_DOCUMENT_APPROVED => 'Sent when an appeal document is approved',
            self::APPEAL_DOCUMENT_REJECTED => 'Sent when an appeal document is rejected',

            self::DOCUMENT_EXPIRING => 'Sent when documents are about to expire',
            self::DOCUMENT_EXPIRED => 'Sent when documents have expired',
        };
    }

    public function defaultChannels(): array
    {
        return match($this) {
            self::USER_REGISTERED => [NotificationChannel::EMAIL],
            self::USER_APPROVED => [NotificationChannel::EMAIL, NotificationChannel::SMS],
            self::USER_REJECTED => [NotificationChannel::EMAIL],
            self::USER_SUSPENDED => [NotificationChannel::EMAIL, NotificationChannel::SMS],
            self::USER_PASSWORD_RESET => [NotificationChannel::EMAIL],

            self::CANDIDATE_APPLIED => [NotificationChannel::EMAIL],
            self::CANDIDATE_APPROVED => [NotificationChannel::EMAIL, NotificationChannel::SMS],
            self::CANDIDATE_REJECTED => [NotificationChannel::EMAIL],
            self::CANDIDATE_DOCUMENTS_REQUESTED => [NotificationChannel::EMAIL],

            self::ELECTION_CREATED => [NotificationChannel::EMAIL],
            self::ELECTION_STARTED => [NotificationChannel::EMAIL, NotificationChannel::SMS, NotificationChannel::IN_APP],
            self::ELECTION_ENDED => [NotificationChannel::EMAIL, NotificationChannel::SMS, NotificationChannel::IN_APP],
            self::ELECTION_CANCELLED => [NotificationChannel::EMAIL, NotificationChannel::SMS],
            self::ELECTION_RESULTS_AVAILABLE => [NotificationChannel::EMAIL, NotificationChannel::IN_APP],

            self::VOTE_CAST => [NotificationChannel::EMAIL],
            self::VOTE_VERIFIED => [NotificationChannel::EMAIL],
            self::VOTE_RECEIPT_REQUESTED => [NotificationChannel::EMAIL],
            self::VOTE_TOKEN_GENERATED => [NotificationChannel::EMAIL, NotificationChannel::SMS, NotificationChannel::IN_APP],

            self::FAILED_LOGIN_ATTEMPT => [NotificationChannel::EMAIL],
            self::SUSPICIOUS_ACTIVITY => [NotificationChannel::EMAIL, NotificationChannel::SMS],
            self::ADMIN_LOGIN => [NotificationChannel::EMAIL],
            self::PASSWORD_CHANGED => [NotificationChannel::EMAIL],

            self::SYSTEM_MAINTENANCE => [NotificationChannel::EMAIL, NotificationChannel::IN_APP],
            self::SYSTEM_BACKUP_COMPLETED => [NotificationChannel::EMAIL],
            self::SYSTEM_ERROR => [NotificationChannel::EMAIL, NotificationChannel::SMS],

            self::NEWSLETTER_SUBSCRIPTION => [NotificationChannel::EMAIL],
            self::CONTACT_FORM_SUBMITTED => [NotificationChannel::EMAIL],
            self::SUPPORT_REQUEST => [NotificationChannel::EMAIL],

            self::APPEAL_SUBMITTED => [NotificationChannel::EMAIL, NotificationChannel::IN_APP],
            self::APPEAL_UNDER_REVIEW => [NotificationChannel::EMAIL, NotificationChannel::IN_APP],
            self::APPEAL_APPROVED => [NotificationChannel::EMAIL, NotificationChannel::SMS, NotificationChannel::IN_APP],
            self::APPEAL_REJECTED => [NotificationChannel::EMAIL, NotificationChannel::IN_APP],
            self::APPEAL_DISMISSED => [NotificationChannel::EMAIL, NotificationChannel::IN_APP],
            self::APPEAL_ASSIGNED => [NotificationChannel::EMAIL, NotificationChannel::IN_APP],
            self::APPEAL_ESCALATED => [NotificationChannel::EMAIL, NotificationChannel::IN_APP],
            self::APPEALS_OVERDUE => [NotificationChannel::EMAIL, NotificationChannel::IN_APP],
            self::APPEALS_NEED_ESCALATION => [NotificationChannel::EMAIL, NotificationChannel::IN_APP],
            self::APPEAL_DEADLINE_REMINDER => [NotificationChannel::EMAIL, NotificationChannel::IN_APP],
            self::APPEAL_DOCUMENT_APPROVED => [NotificationChannel::EMAIL, NotificationChannel::IN_APP],
            self::APPEAL_DOCUMENT_REJECTED => [NotificationChannel::EMAIL, NotificationChannel::IN_APP],

            self::DOCUMENT_EXPIRING => [NotificationChannel::EMAIL, NotificationChannel::IN_APP],
            self::DOCUMENT_EXPIRED => [NotificationChannel::EMAIL, NotificationChannel::SMS, NotificationChannel::IN_APP],
        };
    }

    public function defaultAudience(): NotificationAudience
    {
        return match($this) {
            self::USER_REGISTERED,
            self::USER_APPROVED,
            self::USER_REJECTED,
            self::USER_SUSPENDED,
            self::USER_PASSWORD_RESET,
            self::PASSWORD_CHANGED => NotificationAudience::INDIVIDUAL,

            self::CANDIDATE_APPLIED,
            self::CANDIDATE_APPROVED,
            self::CANDIDATE_REJECTED,
            self::CANDIDATE_DOCUMENTS_REQUESTED => NotificationAudience::INDIVIDUAL,

            self::VOTE_CAST,
            self::VOTE_VERIFIED,
            self::VOTE_RECEIPT_REQUESTED,
            self::VOTE_TOKEN_GENERATED => NotificationAudience::INDIVIDUAL,

            self::ELECTION_CREATED,
            self::ELECTION_STARTED,
            self::ELECTION_ENDED,
            self::ELECTION_CANCELLED,
            self::ELECTION_RESULTS_AVAILABLE => NotificationAudience::ALL_VOTERS,

            self::FAILED_LOGIN_ATTEMPT,
            self::SUSPICIOUS_ACTIVITY,
            self::ADMIN_LOGIN => NotificationAudience::ADMIN,

            self::SYSTEM_MAINTENANCE,
            self::SYSTEM_BACKUP_COMPLETED,
            self::SYSTEM_ERROR => NotificationAudience::ADMIN,

            self::NEWSLETTER_SUBSCRIPTION,
            self::CONTACT_FORM_SUBMITTED,
            self::SUPPORT_REQUEST => NotificationAudience::ADMIN,

            self::APPEAL_SUBMITTED => NotificationAudience::ADMIN,
            self::APPEAL_UNDER_REVIEW,
            self::APPEAL_APPROVED,
            self::APPEAL_REJECTED,
            self::APPEAL_DISMISSED,
            self::APPEAL_ASSIGNED,
            self::APPEAL_ESCALATED,
            self::APPEAL_DEADLINE_REMINDER,
            self::APPEAL_DOCUMENT_APPROVED,
            self::APPEAL_DOCUMENT_REJECTED,
            self::DOCUMENT_EXPIRING,
            self::DOCUMENT_EXPIRED => NotificationAudience::INDIVIDUAL,
            self::APPEALS_OVERDUE,
            self::APPEALS_NEED_ESCALATION => NotificationAudience::ADMIN,
        };
    }

    public function priority(): NotificationPriority
    {
        return match($this) {
            self::SYSTEM_ERROR,
            self::SUSPICIOUS_ACTIVITY,
            self::FAILED_LOGIN_ATTEMPT => NotificationPriority::HIGH,

            self::USER_SUSPENDED,
            self::ELECTION_CANCELLED,
            self::ADMIN_LOGIN,
            self::VOTE_TOKEN_GENERATED => NotificationPriority::NORMAL,

            self::APPEAL_SUBMITTED,
            self::APPEAL_UNDER_REVIEW,
            self::APPEAL_ASSIGNED,
            self::APPEAL_ESCALATED,
            self::APPEALS_OVERDUE,
            self::APPEALS_NEED_ESCALATION,
            self::APPEAL_DEADLINE_REMINDER => NotificationPriority::HIGH,

            self::APPEAL_APPROVED,
            self::APPEAL_REJECTED,
            self::APPEAL_DISMISSED,
            self::APPEAL_DOCUMENT_APPROVED,
            self::APPEAL_DOCUMENT_REJECTED,
            self::DOCUMENT_EXPIRING => NotificationPriority::NORMAL,

            self::DOCUMENT_EXPIRED => NotificationPriority::HIGH,

            default => NotificationPriority::NORMAL,
        };
    }
}