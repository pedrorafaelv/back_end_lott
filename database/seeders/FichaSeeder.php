<?php

namespace Database\Seeders;

use App\Models\Ficha;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FichaSeeder extends Seeder
{
    /**
     * Cuántas fichas crear. Configurable por env: FICHAS_CANTIDAD.
     */
    private int $cantidad;

    /**
     * Prefijo del nombre. Ej: "Caricatura" → "Caricatura 376".
     * Configurable por env: FICHAS_NAME_PREFIX.
     */
    private string $namePrefix;

    /**
     * Extensión de imagen. Configurable por env: FICHAS_IMAGE_EXT.
     */
    private string $imageExt;

    /**
     * Si true, name = id (comportamiento antiguo).
     * Configurable por env: FICHAS_NAME_ES_ID.
     */
    private bool $nameEsId;

    public function __construct()
    {
        $this->cantidad   = (int) env('FICHAS_CANTIDAD', 100);
        $this->namePrefix = (string) env('FICHAS_NAME_PREFIX', 'Caricatura');
        $this->imageExt   = (string) env('FICHAS_IMAGE_EXT', 'png');
        $this->nameEsId   = (bool) env('FICHAS_NAME_ES_ID', false);
    }

    public function run(): void
    {
        if ($this->cantidad <= 0) {
            $this->command->error('FICHAS_CANTIDAD debe ser > 0.');
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        try {
            // Punto de partida: el id más alto actual. Si la tabla está vacía → 0.
            $ultimoId = (int) Ficha::max('id');
            $desde    = $ultimoId + 1;
            $hasta    = $ultimoId + $this->cantidad;

            $now  = now();
            $rows = [];

            for ($i = 0; $i < $this->cantidad; $i++) {
                $numero = $desde + $i;

                $name = $this->nameEsId
                    ? (string) $numero
                    : trim("{$this->namePrefix} {$numero}");

                $rows[] = [
                    // No forzamos 'id': lo asigna auto_increment.
                    // Pero como acabamos de leer max(id), el auto_increment
                    // arrancará exactamente en $desde (siempre que nadie más
                    // inserte entre el max() y el insert()).
                    'name'        => $name,
                    'description' => "icono {$numero}",
                    'image'       => "{$numero}.{$this->imageExt}",
                    'sound'       => '',
                    'active'      => '1',   // varchar(2)
                    'start_date'  => $now,
                    'end_date'    => null,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }

            foreach (array_chunk($rows, 500) as $chunk) {
                Ficha::insert($chunk);
            }

            $this->command->info(
                "✅ Fichas creadas: {$this->cantidad} " .
                "(ids esperados {$desde}..{$hasta}). Total actual: " . Ficha::count()
            );

            // Verificación: confirma que los IDs realmente quedaron en el rango.
            $nuevos = Ficha::whereBetween('id', [$desde, $hasta])->count();
            if ($nuevos !== $this->cantidad) {
                $this->command->warn(
                    "⚠️  Se insertaron {$nuevos} filas en [{$desde},{$hasta}], " .
                    "no {$this->cantidad}. Revisa concurrencia o auto_increment."
                );
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}