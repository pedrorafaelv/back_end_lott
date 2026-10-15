<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegalDocumentVersion extends Model
{
    protected $fillable = [
        'doc_type',
        'version',
        'effective_date',
        'content_hash',
        'content_text',
    ];

    protected $casts = [
        'effective_date' => 'datetime',
    ];

    /**
     * El modelo es inmutable: una vez publicado, no se modifica.
     * Los cambios se hacen publicando una nueva versión.
     */
    protected static function booted(): void
    {
        static::updating(function () {
            throw new \RuntimeException(
                'Las versiones legales son inmutables. Publica una nueva versión.'
            );
        });

        static::deleting(function () {
            throw new \RuntimeException(
                'Las versiones legales no pueden eliminarse.'
            );
        });
    }

    public function acceptances(): HasMany
    {
        return $this->hasMany(TermsAcceptanceLog::class, 'doc_version_id');
    }

    public function scopeActive($query, string $docType)
    {
        return $query->where('doc_type', $docType)
                     ->where('effective_date', '<=', now());
    }
}