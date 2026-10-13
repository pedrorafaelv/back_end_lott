<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ViaCollection extends ResourceCollection
{
    public $collects = ViaResource::class;

    public function toArray($request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total'    => $this->collection->count(),
                'credits'  => $this->collection->where('type', 'credit')->count(),
                'debits'   => $this->collection->where('type', 'debit')->count(),
                'neutral'  => $this->collection->where('type', 'neutral')->count(),
            ],
        ];
    }
}