<?php

namespace App\Services\Notification\Providers;

use App\Enums\Notification\NotificationChannel;
use App\Models\Notification\Notification;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsNotificationProvider implements NotificationProviderInterface
{
    public function getChannel(): NotificationChannel
    {
        return NotificationChannel::SMS;
    }

    public function send(Notification $notification): array
    {
        try {
            $recipient = $this->getRecipient($notification);
            $content = $this->renderContent($notification);

            // Check message length (SMS limit is 160 characters)
            if (strlen($content['body']) > 160) {
                // Truncate message and add continuation note
                $content['body'] = substr($content['body'], 0, 150) . '... (cont.)';
            }

            $result = $this->sendViaApi($recipient['phone'], $content['body']);

            if ($result['success']) {
                // Log successful delivery
                NotificationLog::create([
                    'notification_id' => $notification->id,
                    'channel' => $this->getChannel()->value,
                    'recipient_phone' => $recipient['phone'],
                    'status' => 'sent',
                    'message' => $content['body'],
                    'sent_at' => now(),
                    'provider_response' => $result['response'] ?? null,
                ]);

                return [
                    'success' => true,
                    'message_id' => $result['message_id'] ?? null,
                    'cost' => $this->getCostEstimate(),
                ];
            } else {
                throw new \Exception($result['error'] ?? 'SMS sending failed');
            }

        } catch (\Exception $e) {
            Log::error('SMS notification failed', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);

            // Log failed delivery
            NotificationLog::create([
                'notification_id' => $notification->id,
                'channel' => $this->getChannel()->value,
                'recipient_phone' => $recipient['phone'] ?? null,
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
        return !empty(config('services.sms.api_key')) &&
               !empty(config('services.sms.api_url'));
    }

    public function getConfiguration(): array
    {
        return [
            'provider' => config('services.sms.provider', 'twilio'),
            'api_url' => config('services.sms.api_url'),
            'api_key' => config('services.sms.api_key') ? 'configured' : 'not_configured',
            'sender_id' => config('services.sms.sender_id'),
            'rate_limit' => config('services.sms.rate_limit', 100),
        ];
    }

    public function validateRecipient(array $recipientData): bool
    {
        if (!isset($recipientData['phone'])) {
            return false;
        }

        // Basic phone number validation (remove non-numeric characters)
        $phone = preg_replace('/\D/', '', $recipientData['phone']);

        // Check if it's a valid length (10-15 digits)
        return strlen($phone) >= 10 && strlen($phone) <= 15;
    }

    public function getCostEstimate(): ?float
    {
        // SMS cost varies by provider and destination
        // Return average cost per SMS
        return config('services.sms.cost_per_sms', 0.05);
    }

    private function getRecipient(Notification $notification): array
    {
        $user = $notification->notifiable;

        return [
            'phone' => $user->phone_number,
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
            // Try to find the template by ID from the SMS templates table
            $template = \App\Models\SmsTemplate::find($templateId);
        }

        if (!$template) {
            // Fallback: get template by event type
            $template = $notificationService->getTemplate($notification->type, $notification->channel);
        }

        if ($template) {
            $rendered = $notificationService->renderTemplate($template, $notification->data);
            return [
                'body' => $rendered['message'],
            ];
        }

        // Fallback for notifications without templates
        return [
            'body' => $notification->data['message'] ?? 'You have a new notification.',
        ];
    }

    private function sendViaApi(string $phone, string $message): array
    {
        $provider = config('services.sms.provider', 'twilio');

        switch ($provider) {
            case 'twilio':
                return $this->sendViaTwilio($phone, $message);
            case 'aws':
                return $this->sendViaAWS($phone, $message);
            case 'custom':
                return $this->sendViaCustom($phone, $message);
            default:
                throw new \Exception("Unsupported SMS provider: {$provider}");
        }
    }

    private function sendViaTwilio(string $phone, string $message): array
    {
        try {
            $response = Http::withBasicAuth(
                config('services.twilio.sid'),
                config('services.twilio.token')
            )->post("https://api.twilio.com/2010-04-01/Accounts/" . config('services.twilio.sid') . "/Messages.json", [
                'From' => config('services.twilio.from'),
                'To' => $phone,
                'Body' => $message,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message_id' => $data['sid'] ?? null,
                    'response' => $data,
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response->body(),
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function sendViaAWS(string $phone, string $message): array
    {
        // AWS SNS implementation would go here
        // For now, return not implemented
        return [
            'success' => false,
            'error' => 'AWS SMS provider not implemented',
        ];
    }

    private function sendViaCustom(string $phone, string $message): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.sms.api_key'),
                'Content-Type' => 'application/json',
            ])->post(config('services.sms.api_url'), [
                'to' => $phone,
                'message' => $message,
                'from' => config('services.sms.sender_id'),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message_id' => $data['message_id'] ?? null,
                    'response' => $data,
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response->body(),
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}