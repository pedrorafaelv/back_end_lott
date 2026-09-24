<?php

namespace App\Enums;

enum WithdrawalStatus: string
{
    case PENDING   = 'pending';
    case APPROVED  = 'approved';
    case REJECTED  = 'rejected';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING   => 'Pendiente',
            self::APPROVED  => 'Aprobada',
            self::REJECTED  => 'Rechazada',
            self::COMPLETED => 'Completada',
            self::CANCELLED => 'Cancelada',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::REJECTED, self::COMPLETED, self::CANCELLED]);
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match ($this) {
            self::PENDING   => in_array($newStatus, [self::APPROVED, self::REJECTED, self::CANCELLED]),
            self::APPROVED  => in_array($newStatus, [self::COMPLETED, self::REJECTED, self::CANCELLED]),
            default         => false,
        };
    }
}