<?php

namespace App\Enums;

enum WithdrawalAction: string
{
    case CREATED   = 'created';
    case APPROVED  = 'approved';
    case REJECTED  = 'rejected';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::CREATED   => 'Creada',
            self::APPROVED  => 'Aprobada',
            self::REJECTED  => 'Rechazada',
            self::COMPLETED => 'Completada',
            self::CANCELLED => 'Cancelada',
        };
    }
}