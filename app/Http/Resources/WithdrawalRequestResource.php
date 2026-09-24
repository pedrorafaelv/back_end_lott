<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'user_id'       => $this->user_id,
            'user'          => $this->whenLoaded('user', fn () => [
                'id'    => $this->user->id,
                'name'  => trim($this->user->name . ' ' . ($this->user->last_name ?? '')),
                'email' => $this->user->email,
            ]),
            'account_id'    => $this->account_id,
            'via_id'        => $this->via_id,
            'via'           => $this->whenLoaded('via', fn () => $this->via ? [
                'id'    => $this->via->id,
                'code'  => $this->via->code,
                'label' => $this->via->label,
                'icon'  => $this->via->icon,
                'color' => $this->via->color,
            ] : null),
            'amount'        => (float) $this->amount,
            'commission'    => (float) $this->commission,
            'net_amount'    => (float) $this->net_amount,
            'currency_code' => $this->currency_code,
            'status'        => $this->status->value,
            'payment_data'  => $this->payment_data,
            'user_notes'    => $this->user_notes,
            'admin_notes'   => $this->admin_notes,
            'rejection_reason'      => $this->rejection_reason,
            'transaction_reference' => $this->transaction_reference,
            'approved_at'   => $this->approved_at?->toIso8601String(),
            'rejected_at'   => $this->rejected_at?->toIso8601String(),
            'completed_at'  => $this->completed_at?->toIso8601String(),
            'created_at'    => $this->created_at->toIso8601String(),
            'updated_at'    => $this->updated_at->toIso8601String(),
        ];
    }
}