<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmsTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'message_template',
        'max_length',
        'variables',
        'from_number',
        'estimated_cost',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
        'max_length' => 'integer',
        'estimated_cost' => 'decimal:4',
    ];

    /**
     * Get the user who created this template
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get only active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by event type
     */
    public function scopeForEvent($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Render the SMS template with given data
     */
    public function render(array $data = []): string
    {
        $message = $this->renderTemplate($this->message_template, $data);

        // Truncate if exceeds max length
        if (strlen($message) > $this->max_length) {
            $message = substr($message, 0, $this->max_length - 3) . '...';
        }

        return $message;
    }

    /**
     * Render a template string with data
     */
    private function renderTemplate(string $template, array $data): string
    {
        // Add platform defaults
        $data = array_merge($data, [
            'platform_name' => config('ayapoll.platform_name', 'AYApoll'),
        ]);

        // Replace {{ variable }} patterns
        foreach ($data as $key => $value) {
            $template = str_replace("{{ {$key} }}", $value, $template);
        }

        return $template;
    }

    /**
     * Get available variables for this template
     */
    public function getAvailableVariables(): array
    {
        return $this->variables ?? [];
    }

    /**
     * Check if rendered message exceeds length limit
     */
    public function willExceedLimit(array $data = []): bool
    {
        $rendered = $this->renderTemplate($this->message_template, $data);
        return strlen($rendered) > $this->max_length;
    }

    /**
     * Get character count for rendered message
     */
    public function getCharacterCount(array $data = []): int
    {
        return strlen($this->render($data));
    }
}
