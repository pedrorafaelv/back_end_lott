<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TermsAcceptanceLog extends Model
{
    protected $table = 'terms_acceptance_log';

    protected $fillable = [
        'user_id',
        'doc_version_id',
        'accepted_at',
        'ip_address',
        'user_agent',
        'source_url',
        'superseded_by',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    /**
     * El log es append-only: nunca se actualiza ni se borra.
     * La única actualización permitida es marcar `superseded_by`
     * cuando se acepta una nueva versión.
     */
    protected static function booted(): void
    {
        static::deleting(function () {
            throw new \RuntimeException(
                'El log de aceptaciones es inmutable y no puede eliminarse.'
            );
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(LegalDocumentVersion::class, 'doc_version_id');
    }

    public function supersededBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'superseded_by');
    }

    public function scopeCurrent($query)
    {
        return $query->whereNull('superseded_by');
    }
}