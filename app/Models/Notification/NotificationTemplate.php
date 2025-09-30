<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Enums\Notification\NotificationChannel;

class NotificationTemplate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'event_type',
        'channel',
        'subject',
        'body_template',
        'variables',
        'is_active',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'channel' => NotificationChannel::class,
            'variables' => 'json',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relationships
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForEvent($query, $eventType)
    {
        return $query->where('event_type', '=', $eventType);
    }

    public function scopeForChannel($query, NotificationChannel $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Helper methods
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function getChannelLabel(): string
    {
        return $this->channel->label();
    }

    public function getEventTypeLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->event_type));
    }

    public function hasSubject(): bool
    {
        return $this->channel->supportsSubject();
    }

    public function getVariablesList(): array
    {
        return $this->variables ?? [];
    }

    public function renderTemplate(array $data): array
    {
        $subject = $this->subject;
        $body = $this->body_template;

        // Replace template variables
        foreach ($data as $key => $value) {
            $placeholder = '{{ ' . $key . ' }}';
            $subject = str_replace($placeholder, $value, $subject);
            $body = str_replace($placeholder, $value, $body);
        }

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }

    public function validateTemplate(): array
    {
        $errors = [];

        // Check if template has required placeholders
        $requiredVariables = $this->getVariablesList();
        
        foreach ($requiredVariables as $variable) {
            $placeholder = '{{ ' . $variable . ' }}';
            
            if ($this->hasSubject() && strpos($this->subject, $placeholder) === false && 
                strpos($this->body_template, $placeholder) === false) {
                $errors[] = "Template missing required variable: {$variable}";
            }
        }

        // Check for orphaned placeholders
        $pattern = '/\{\{\s*(\w+)\s*\}\}/';
        $allText = ($this->subject ?? '') . ' ' . $this->body_template;
        
        if (preg_match_all($pattern, $allText, $matches)) {
            $usedVariables = array_unique($matches[1]);
            $undefinedVariables = array_diff($usedVariables, $requiredVariables);
            
            foreach ($undefinedVariables as $variable) {
                $errors[] = "Undefined variable in template: {$variable}";
            }
        }

        return $errors;
    }

    public function duplicate(): self
    {
        $copy = $this->replicate();
        $copy->event_type = $this->event_type . '_copy';
        $copy->is_active = false;
        $copy->created_by = null;
        $copy->save();

        return $copy;
    }

    public function getPreview(array $sampleData = []): array
    {
        $defaultData = [
            'user_name' => 'John Doe',
            'platform_name' => config('ayapoll.platform_name', 'AYApoll'),
            'election_title' => 'Sample Election',
            'verification_code' => '123456',
            'expires_at' => now()->addHours(24)->format('M j, Y \a\t g:i A'),
        ];

        $data = array_merge($defaultData, $sampleData);
        
        return $this->renderTemplate($data);
    }

    /**
     * Static methods
     */
    public static function getForEventAndChannel(string $eventType, NotificationChannel $channel): ?self
    {
        return static::active()
            ->forEvent($eventType)
            ->forChannel($channel)
            ->first();
    }

    public static function getAvailableEvents(): array
    {
        return [
            'user_registered' => 'User Registration',
            'user_approved' => 'User Approved',
            'user_rejected' => 'User Rejected',
            'email_verification' => 'Email Verification',
            'phone_verification' => 'Phone Verification',
            'login_verification' => 'Login Verification',
            'candidate_application_submitted' => 'Candidate Application Submitted',
            'candidate_approved' => 'Candidate Approved',
            'candidate_rejected' => 'Candidate Rejected',
            'election_created' => 'Election Created',
            'election_started' => 'Election Started',
            'election_ending_soon' => 'Election Ending Soon',
            'election_ended' => 'Election Ended',
            'vote_cast' => 'Vote Cast',
            'vote_receipt' => 'Vote Receipt',
            'system_maintenance' => 'System Maintenance',
            'security_alert' => 'Security Alert',
        ];
    }

    public static function getDefaultVariables(string $eventType): array
    {
        return match($eventType) {
            'user_registered' => ['user_name', 'platform_name', 'verification_url'],
            'user_approved' => ['user_name', 'platform_name', 'login_url'],
            'user_rejected' => ['user_name', 'platform_name', 'reason', 'support_email'],
            'email_verification' => ['user_name', 'verification_code', 'expires_at'],
            'phone_verification' => ['platform_name', 'verification_code'],
            'login_verification' => ['user_name', 'platform_name', 'verification_code', 'expires_at'],
            'election_started' => ['user_name', 'election_title', 'ends_at', 'voting_url', 'platform_name'],
            'vote_cast' => ['user_name', 'election_title', 'receipt_hash', 'cast_at'],
            'vote_receipt' => ['user_name', 'election_title', 'receipt_hash', 'verification_url', 'cast_at'],
            default => ['user_name', 'platform_name'],
        };
    }
}