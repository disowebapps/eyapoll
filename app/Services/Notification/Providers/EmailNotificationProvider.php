<?php

namespace App\Services\Notification\Providers;

use App\Enums\Notification\NotificationChannel;
use App\Models\Notification\Notification;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailNotificationProvider implements NotificationProviderInterface
{
    public function getChannel(): NotificationChannel
    {
        return NotificationChannel::EMAIL;
    }

    public function send(Notification $notification): array
    {
        try {
            $recipient = $this->getRecipient($notification);
            $content = $this->renderContent($notification);

            // Send email using Laravel Mail
            Mail::raw($content['body'], function ($message) use ($recipient, $content) {
                $message->to($recipient['email'])
                        ->subject($content['subject'])
                        ->from(
                            config('mail.from.address', 'noreply@ayapoll.com'),
                            config('mail.from.name', 'AYApoll')
                        );
            });

            // Log successful delivery
            NotificationLog::create([
                'notification_id' => $notification->id,
                'channel' => $this->getChannel()->value,
                'recipient_email' => $recipient['email'],
                'status' => 'sent',
                'message' => $content['body'],
                'sent_at' => now(),
            ]);

            return [
                'success' => true,
                'message_id' => null, // Laravel doesn't return message ID by default
                'cost' => 0.0,
            ];

        } catch (\Exception $e) {
            Log::error('Email notification failed', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);

            // Log failed delivery
            NotificationLog::create([
                'notification_id' => $notification->id,
                'channel' => $this->getChannel()->value,
                'recipient_email' => $recipient['email'] ?? null,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function isAvailable(): bool
    {
        return config('mail.default') !== null &&
               !empty(config('mail.from.address'));
    }

    public function getConfiguration(): array
    {
        return [
            'driver' => config('mail.default'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
            'encryption' => config('mail.encryption'),
            'host' => config('mail.host'),
            'port' => config('mail.port'),
        ];
    }

    public function validateRecipient(array $recipientData): bool
    {
        return isset($recipientData['email']) &&
               filter_var($recipientData['email'], FILTER_VALIDATE_EMAIL);
    }

    public function getCostEstimate(): ?float
    {
        // Email is typically free for transactional emails
        return 0.0;
    }

    private function getRecipient(Notification $notification): array
    {
        $user = $notification->notifiable;

        return [
            'email' => $user->email,
            'name' => $user->full_name,
        ];
    }

    private function renderContent(Notification $notification): array
    {
        // Use NotificationService to render template
        $notificationService = app(\App\Services\Notification\NotificationService::class);

        // Get template from notification data (stored during creation)
        $templateId = $notification->data['template_id'] ?? null;
        $template = null;

        if ($templateId) {
            // Try to find the template by ID from the appropriate table
            $template = \App\Models\EmailTemplate::find($templateId);
        }

        if (!$template) {
            // Fallback: get template by event type
            $template = $notificationService->getTemplate($notification->type, $notification->channel);
        }

        if ($template) {
            return $notificationService->renderTemplate($template, $notification->data);
        }

        // Fallback for notifications without templates
        return [
            'subject' => 'Notification from AYApoll',
            'body' => $notification->data['message'] ?? 'You have a new notification.',
        ];
    }
}