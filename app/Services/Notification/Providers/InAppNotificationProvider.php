<?php

namespace App\Services\Notification\Providers;

use App\Enums\Notification\NotificationChannel;
use App\Models\Notification\Notification;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Log;

class InAppNotificationProvider implements NotificationProviderInterface
{
    public function getChannel(): NotificationChannel
    {
        return NotificationChannel::IN_APP;
    }

    public function send(Notification $notification): array
    {
        try {
            $recipient = $this->getRecipient($notification);
            $content = $this->renderContent($notification);

            // For in-app notifications, we just mark the notification as sent
            // The notification is already stored in the database and will be
            // displayed to the user through the UI

            // Log successful delivery
            NotificationLog::create([
                'notification_id' => $notification->id,
                'channel' => $this->getChannel()->value,
                'recipient_id' => $recipient['id'],
                'status' => 'sent',
                'message' => $content['body'],
                'sent_at' => now(),
            ]);

            return [
                'success' => true,
                'message_id' => $notification->id,
                'cost' => 0.0,
            ];

        } catch (\Exception $e) {
            Log::error('In-app notification failed', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);

            // Log failed delivery
            NotificationLog::create([
                'notification_id' => $notification->id,
                'channel' => $this->getChannel()->value,
                'recipient_id' => $recipient['id'] ?? null,
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
        // In-app notifications are always available since they use the database
        return true;
    }

    public function getConfiguration(): array
    {
        return [
            'storage' => 'database',
            'retention_days' => config('notifications.channels.in_app.retention_days', 30),
            'max_notifications' => config('notifications.channels.in_app.max_per_user', 100),
            'real_time' => true,
        ];
    }

    public function validateRecipient(array $recipientData): bool
    {
        // For in-app notifications, we need a valid user ID
        return isset($recipientData['id']) && is_numeric($recipientData['id']);
    }

    public function getCostEstimate(): ?float
    {
        // In-app notifications are free
        return 0.0;
    }

    private function getRecipient(Notification $notification): array
    {
        $user = $notification->notifiable;

        return [
            'id' => $user->id,
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
            // Try to find the template by ID from the in-app templates table
            $template = \App\Models\InAppTemplate::find($templateId);
        }

        if (!$template) {
            // Fallback: get template by event type
            $template = $notificationService->getTemplate($notification->type, $notification->channel);
        }

        if ($template) {
            $rendered = $notificationService->renderTemplate($template, $notification->data);
            return [
                'title' => $rendered['title'] ?? 'Notification',
                'body' => $rendered['message'] ?? $notification->data['message'] ?? 'You have a new notification.',
            ];
        }

        // Fallback for notifications without templates
        return [
            'title' => 'Notification',
            'body' => $notification->data['message'] ?? 'You have a new notification.',
        ];
    }
}