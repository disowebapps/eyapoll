<?php

namespace App\Services\Notification\Providers;

use App\Enums\Notification\NotificationChannel;
use App\Models\Notification\Notification;

interface NotificationProviderInterface
{
    /**
     * Get the channel this provider handles
     */
    public function getChannel(): NotificationChannel;

    /**
     * Send a notification
     */
    public function send(Notification $notification): array;

    /**
     * Check if this provider is configured and available
     */
    public function isAvailable(): bool;

    /**
     * Get provider-specific configuration
     */
    public function getConfiguration(): array;

    /**
     * Validate recipient information for this channel
     */
    public function validateRecipient(array $recipientData): bool;

    /**
     * Get the cost estimate for sending this notification
     */
    public function getCostEstimate(): ?float;
}