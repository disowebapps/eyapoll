<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Enums\Auth\DocumentType;
use Illuminate\Support\Facades\Auth;

class IdDocument extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'document_type',
        'file_path',
        'file_hash',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'assigned_reviewer_id',
        'assigned_at',
        'escalated_at',
        'review_started_at',
        'review_completed_at',
        'perceptual_hash',
        'ocr_text',
        'authenticity_score',
        'verification_status',
        'verification_errors',
        'verified_at',
        'version_number',
        'parent_id',
        'expiry_date',
        'country_code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'file_path',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'document_type' => DocumentType::class,
            'file_path' => 'encrypted',
            'reviewed_at' => 'datetime',
            'assigned_at' => 'datetime',
            'escalated_at' => 'datetime',
            'review_started_at' => 'datetime',
            'review_completed_at' => 'datetime',
            'verified_at' => 'datetime',
            'verification_errors' => 'array',
            'expiry_date' => 'date',
        ];
    }
    


    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function assignedReviewer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Admin::class, 'assigned_reviewer_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(IdDocument::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(IdDocument::class, 'parent_id');
    }

    /**
     * Scopes
     */
    public function scopeWithCommonRelationships($query)
    {
        return $query->with([
            'user:id,first_name,last_name,email',
            'reviewer:id,name',
            'assignedReviewer:id,name'
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeLatestVersion($query)
    {
        return $query->whereRaw('id IN (SELECT MAX(id) FROM id_documents GROUP BY user_id, document_type)');
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                    ->where('expiry_date', '>', now());
    }

    /**
     * Helper methods
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function canBeApproved(): bool
    {
        return $this->isPending();
    }

    public function canBeRejected(): bool
    {
        return $this->isPending();
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'approved' => 'green',
            'rejected' => 'red',
            default => 'gray',
        };
    }

    public function getDocumentTypeLabel(): string
    {
        return $this->document_type->label();
    }

    public function getFileUrl(): string
    {
        $secureFileService = app(\App\Services\Security\SecureFileService::class);
        return $secureFileService->generateAccessUrl(decrypt($this->file_path), 60); // 1 hour
    }

    public function getFileSizeFormatted(): string
    {
        $secureFileService = app(\App\Services\Security\SecureFileService::class);
        $filename = decrypt($this->file_path);

        if (!$secureFileService->exists($filename)) {
            return 'File not found';
        }

        $bytes = $secureFileService->size($filename);

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    public function isImage(): bool
    {
        $extension = pathinfo(decrypt($this->file_path), PATHINFO_EXTENSION);
        return in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']);
    }

    public function isPdf(): bool
    {
        $extension = pathinfo(decrypt($this->file_path), PATHINFO_EXTENSION);
        return strtolower($extension) === 'pdf';
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon($days = 30): bool
    {
        return $this->expiry_date &&
               $this->expiry_date->isFuture() &&
               $this->expiry_date->diffInDays(now()) <= $days;
    }

    public function getDaysUntilExpiry(): ?int
    {
        return $this->expiry_date ? now()->diffInDays($this->expiry_date, false) : null;
    }

    public function getCountryName(): ?string
    {
        if (!$this->country_code) {
            return null;
        }

        // You might want to use a package like league/iso3166 for proper country names
        return $this->country_code; // Placeholder
    }
}