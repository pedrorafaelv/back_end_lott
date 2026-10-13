<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ViaResource extends JsonResource
{
    /**
     * Transforma el recurso en un array para la respuesta JSON.
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'code'          => $this->code,
            'label'         => $this->label,
            'icon'          => $this->icon,
            'color'         => $this->color,
            'type'          => $this->type,
            'type_label'    => $this->typeLabel(),   // helper bonito
            'is_active'     => (bool) $this->is_active,
            'display_order' => (int) $this->display_order,
            'created_at'    => $this->created_at?->toIso8601String(),
            'updated_at'    => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Etiqueta legible del tipo, útil para el frontend.
     */
    private function typeLabel(): string
    {
        return match ($this->type) {
            'credit'  => 'Entrada',
            'debit'   => 'Salida',
            'neutral' => 'Neutral',
            default   => 'Desconocido',
        };
    }
}