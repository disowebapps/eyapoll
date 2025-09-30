<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'subject',
        'body_template',
        'html_template',
        'variables',
        'from_name',
        'from_email',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
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
     * Render the template with given data
     */
    public function render(array $data = []): array
    {
        $rendered = [
            'subject' => $this->renderTemplate($this->subject, $data),
            'body' => $this->renderTemplate($this->body_template, $data),
            'html' => $this->html_template ? $this->renderTemplate($this->html_template, $data) : null,
            'from_name' => $this->from_name,
            'from_email' => $this->from_email,
        ];

        return $rendered;
    }

    /**
     * Render a template string with data
     */
    private function renderTemplate(string $template, array $data): string
    {
        // Add platform defaults
        $data = array_merge($data, [
            'platform_name' => config('ayapoll.platform_name', 'AYApoll'),
            'current_year' => date('Y'),
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
     * Check if template has HTML version
     */
    public function hasHtmlVersion(): bool
    {
        return !empty($this->html_template);
    }
}
