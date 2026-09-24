<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawalDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return array_merge((new WithdrawalResource($this))->toArray($request), [
            'account' => $this->whenLoaded('account', fn () => $this->account ? [
                'id'            => $this->account->id,
                'currency_code' => $this->account->currency_code,
                'amount'        => (float) $this->account->amount,
            ] : null),
            'approved_by'  => $this->whenLoaded('approvedBy', fn () => $this->approvedBy ? ['id' => $this->approvedBy->id, 'name' => $this->approvedBy->name] : null),
            'rejected_by'  => $this->whenLoaded('rejectedBy', fn () => $this->rejectedBy ? ['id' => $this->rejectedBy->id, 'name' => $this->rejectedBy->name] : null),
            'completed_by' => $this->whenLoaded('completedBy', fn () => $this->completedBy ? ['id' => $this->completedBy->id, 'name' => $this->completedBy->name] : null),
            'logs' => $this->whenLoaded('logs', fn () => $this->logs->map(fn ($log) => [
                'id'         => $log->id,
                'action'     => $log->action->value,
                'notes'      => $log->notes,
                'metadata'   => $log->metadata,
                'admin'      => $log->admin ? ['id' => $log->admin->id, 'name' => $log->admin->name] : null,
                'created_at' => $log->created_at->toIso8601String(),
            ])),
        ]);
    }
}