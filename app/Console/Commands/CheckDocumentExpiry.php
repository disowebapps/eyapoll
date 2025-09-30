<?php

namespace App\Console\Commands;

use App\Services\DocumentManagementService;
use App\Services\Notification\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckDocumentExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:check-expiry {--days=30 : Days before expiry to check} {--notify : Send notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for documents expiring soon and send notifications';

    protected DocumentManagementService $documentService;
    protected NotificationService $notificationService;

    public function __construct(DocumentManagementService $documentService, NotificationService $notificationService)
    {
        parent::__construct();
        $this->documentService = $documentService;
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $shouldNotify = $this->option('notify');

        $this->info("Checking for documents expiring in {$days} days or less...");

        // Get expiring documents
        $expiringDocuments = $this->documentService->getExpiringDocuments($days);
        $expiredDocuments = $this->documentService->getExpiredDocuments();

        $this->info("Found {$expiringDocuments->count()} documents expiring soon");
        $this->info("Found {$expiredDocuments->count()} expired documents");

        if ($shouldNotify) {
            $this->sendNotifications($expiringDocuments, $days);
            $this->sendExpiredNotifications($expiredDocuments);
        }

        // Log summary
        Log::info("Document expiry check completed: {$expiringDocuments->count()} expiring, {$expiredDocuments->count()} expired");

        $this->info('Document expiry check completed.');
    }

    /**
     * Send notifications for expiring documents
     */
    private function sendNotifications($documents, int $days): void
    {
        $this->info("Sending expiry notifications...");

        foreach ($documents as $document) {
            try {
                $this->sendExpiryNotification($document, $days);
                $this->info("Notification sent for document ID: {$document->id}");
            } catch (\Exception $e) {
                $this->error("Failed to send notification for document ID: {$document->id} - {$e->getMessage()}");
                Log::error("Document expiry notification failed", [
                    'document_id' => $document->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Send notifications for expired documents
     */
    private function sendExpiredNotifications($documents): void
    {
        $this->info("Sending expired document notifications...");

        foreach ($documents as $document) {
            try {
                $this->sendExpiredNotification($document);
                $this->info("Expired notification sent for document ID: {$document->id}");
            } catch (\Exception $e) {
                $this->error("Failed to send expired notification for document ID: {$document->id} - {$e->getMessage()}");
                Log::error("Document expired notification failed", [
                    'document_id' => $document->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Send expiry notification for a single document
     */
    private function sendExpiryNotification($document, int $days): void
    {
        $owner = $document->user ?? $document->candidate;
        $recipientId = $owner->id;
        $recipientType = $document->user ? 'user' : 'candidate';

        $message = "Your {$document->getDocumentTypeLabel()} document will expire in {$days} days. Please update it soon.";

        $notifiable = $document->user ?? $document->candidate;
        $this->notificationService->sendByEvent(
            \App\Enums\Notification\NotificationEventType::DOCUMENT_EXPIRING,
            $notifiable,
            [
                'document_id' => $document->id,
                'document_type' => $document->document_type,
                'expiry_date' => $document->expiry_date->toDateString(),
                'days_until_expiry' => $days,
            ]
        );
    }

    /**
     * Send expired notification for a single document
     */
    private function sendExpiredNotification($document): void
    {
        $owner = $document->user ?? $document->candidate;
        $recipientId = $owner->id;
        $recipientType = $document->user ? 'user' : 'candidate';

        $message = "Your {$document->getDocumentTypeLabel()} document has expired. Please update it immediately.";

        $notifiable = $document->user ?? $document->candidate;
        $this->notificationService->sendByEvent(
            \App\Enums\Notification\NotificationEventType::DOCUMENT_EXPIRED,
            $notifiable,
            [
                'document_id' => $document->id,
                'document_type' => $document->document_type,
                'expiry_date' => $document->expiry_date->toDateString(),
            ]
        );
    }
}
