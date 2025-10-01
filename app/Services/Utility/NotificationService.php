<?php

namespace App\Services\Utility;

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function sendEmail(string $to, string $subject, string $message, array $context = []): void
    {
        try {
            Mail::raw($message, function ($mail) use ($to, $subject) {
                $mail->to($to)->subject($subject);
            });
        } catch (\Exception $e) {
            Log::error('Failed to send email notification', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function sendSms(string $to, string $message): void
    {
        // In a real implementation, integrate with SMS provider
        Log::info('SMS notification sent', ['to' => $to, 'message' => $message]);
    }

    public function sendPushNotification(string $to, string $title, string $message, array $data = []): void
    {
        // In a real implementation, integrate with push notification service
        Log::info('Push notification sent', [
            'to' => $to,
            'title' => $title,
            'message' => $message,
            'data' => $data
        ]);
    }

    public function sendAlertNotification(string $channel, string $message, array $context = []): void
    {
        switch ($channel) {
            case 'email':
                $this->sendEmail(
                    $context['email'] ?? config('app.admin_email'),
                    'System Alert',
                    $message,
                    $context
                );
                break;
            case 'sms':
                $this->sendSms($context['phone'] ?? '', $message);
                break;
            case 'push':
                $this->sendPushNotification(
                    $context['user_id'] ?? '',
                    'System Alert',
                    $message,
                    $context
                );
                break;
            default:
                Log::warning('Unknown notification channel', ['channel' => $channel]);
        }
    }
}
