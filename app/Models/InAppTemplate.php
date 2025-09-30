<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InAppTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'title',
        'message_template',
        'icon',
        'action_url',
        'action_text',
        'priority',
        'retention_days',
        'variables',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
        'retention_days' => 'integer',
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
     * Scope to filter by priority
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Render the in-app notification template with given data
     */
    public function render(array $data = []): array
    {
        return [
            'title' => $this->renderTemplate($this->title, $data),
            'message' => $this->renderTemplate($this->message_template, $data),
            'icon' => $this->icon,
            'action_url' => $this->renderTemplate($this->action_url ?? '', $data),
            'action_text' => $this->renderTemplate($this->action_text ?? '', $data),
            'priority' => $this->priority,
            'retention_days' => $this->retention_days,
        ];
    }

    /**
     * Render a template string with data
     */
    private function renderTemplate(string $template, array $data): string
    {
        if (empty($template)) {
            return '';
        }

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
     * Check if template has an action button
     */
    public function hasAction(): bool
    {
        return !empty($this->action_url) && !empty($this->action_text);
    }

    /**
     * Get priority color for UI
     */
    public function getPriorityColor(): string
    {
        return match($this->priority) {
            'urgent' => 'red',
            'high' => 'orange',
            'normal' => 'blue',
            'low' => 'gray',
            default => 'blue',
        };
    }

    /**
     * Get priority icon
     */
    public function getPriorityIcon(): string
    {
        return match($this->priority) {
            'urgent' => 'heroicon-o-exclamation-circle',
            'high' => 'heroicon-o-exclamation-triangle',
            'normal' => 'heroicon-o-information-circle',
            'low' => 'heroicon-o-minus-circle',
            default => 'heroicon-o-information-circle',
        };
    }
}
