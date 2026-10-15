<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DepositRequestResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'amount'        => $this->amount,
            'status'        => $this->status,

            // 👇 Moneda
            'currency_id'   => $this->currency_id,
            'currency_code' => $this->whenLoaded('currency', fn () => $this->currency->code),
            'currency_name' => $this->whenLoaded('currency', fn () => $this->currency->name),

            // 👇 Relaciones
            'user'          => $this->whenLoaded('user'),
            'via'           => $this->whenLoaded('via'),
            'approved_by'   => $this->whenLoaded('approvedBy'),

            'created_at'    => $this->created_at?->toIso8601String(),
            'approved_at'   => $this->approved_at?->toIso8601String(),
            'completed_at'  => $this->completed_at?->toIso8601String(),
        ];
    }
}