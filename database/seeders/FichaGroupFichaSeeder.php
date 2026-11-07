<?php

namespace Database\Seeders;

use App\Models\Ficha;
use App\Models\GroupFicha;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FichaGroupFichaSeeder extends Seeder
{
    /**
     * Cuántas fichas asignar al groupficha. Configurable por env: FICHAS_POR_TEMA.
     */
    private int $fichasPorTema;

    /**
     * ID del groupficha destino. Configurable por env: GROUPFICHA_ID.
     * (Puedes correr el seeder varias veces cambiando esta env para
     * ir poblando temas uno a uno.)
     */
    private int $groupFichaId;

    public function __construct()
    {
        $this->fichasPorTema = (int) env('FICHAS_POR_TEMA', 100);
        $this->groupFichaId  = (int) env('GROUPFICHA_ID', 1);
    }

    public function run(): void
    {
        if ($this->fichasPorTema <= 0) {
            $this->command->error('FICHAS_POR_TEMA debe ser > 0.');
            return;
        }

        $tema = GroupFicha::find($this->groupFichaId, ['id', 'name']);
        if (!$tema) {
            $this->command->error("No existe groupficha id={$this->groupFichaId}. Corre GroupFichaSeeder primero.");
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        try {
            // Punto de partida: último ficha_id usado en la pivote.
            // Si la tabla está vacía → 0.
            $ultimoFichaId = (int) DB::table('ficha_groupfichas')->max('ficha_id');
            $desde = $ultimoFichaId + 1;
            $hasta = $ultimoFichaId + $this->fichasPorTema;

            // Solo tomamos fichas que existan realmente en `fichas`.
            $ids = Ficha::whereBetween('id', [$desde, $hasta])
                ->orderBy('id')
                ->pluck('id')
                ->all();

            if (empty($ids)) {
                $this->command->error(
                    "No hay fichas en el rango [{$desde},{$hasta}]. " .
                    "Corre FichaSeeder primero (necesitas al menos {$this->fichasPorTema} fichas nuevas)."
                );
                return;
            }

            if (count($ids) < $this->fichasPorTema) {
                $this->command->warn(
                    "⚠️  Solo hay " . count($ids) . " fichas en [{$desde},{$hasta}] " .
                    "de las {$this->fichasPorTema} esperadas. Se asignarán las disponibles."
                );
            }

            $now  = now();
            $rows = [];
            foreach ($ids as $fichaId) {
                $rows[] = [
                    'ficha_id'      => $fichaId,
                    'groupficha_id' => $this->groupFichaId,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
            }

            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('ficha_groupfichas')->insert($chunk);
            }

            $this->command->info(
                "✅ Pivote: " . count($rows) . " fichas asignadas al tema {$this->groupFichaId} ({$tema->name}) " .
                "usando ids [{$desde}.." . ($desde + count($rows) - 1) . "]."
            );
            $this->command->line(
                "   Total en pivote para este tema: " .
                DB::table('ficha_groupfichas')->where('groupficha_id', $this->groupFichaId)->count()
            );
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}