<?php

namespace App\Services;

use App\Models\Card;
use App\Models\Ficha;
use App\Models\GroupFicha;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CardGenerationService
{
    /** Posiciones de cada combinación (0-indexed sobre el array de 25 fichas). */
    private const COMBINATIONS = [
        'comb01' => [0, 1, 2, 3, 4],
        'comb02' => [5, 6, 7, 8, 9],
        'comb03' => [10, 11, 12, 13, 14],
        'comb04' => [15, 16, 17, 18, 19],
        'comb05' => [20, 21, 22, 23, 24],
        'comb06' => [0, 5, 10, 15, 20],
        'comb07' => [1, 6, 11, 16, 21],
        'comb08' => [2, 7, 12, 17, 22],
        'comb09' => [3, 8, 13, 18, 23],
        'comb10' => [4, 9, 14, 19, 24],
        'comb11' => [0, 6, 12, 18, 24],
        'comb12' => [4, 8, 12, 16, 20],
        'comb13' => [0, 4, 12, 20, 24],
        'comb14' => [2, 10, 12, 14, 22],
    ];

    /**
     * Genera $count cartones únicos para un groupficha.
     *
     * @return array{created:int, cards:array<int,array{id:int,name:?string,groupFicha_id:int}>}
     */
    public function generateForGroupFicha(int $groupFichaId, int $count): array
    {
        $groupFicha = GroupFicha::find($groupFichaId);
        if (!$groupFicha) {
            throw ValidationException::withMessages([
                'groupFicha_id' => "El groupficha {$groupFichaId} no existe.",
            ]);
        }

        // Una sola query: fichas activas del tema.
        $fichas = Ficha::query()
            ->where('active', 1)
            ->whereHas('GroupFichas', fn ($q) => $q->where('groupfichas.id', $groupFichaId))
            ->get(['id', 'name']);

        $n = $fichas->count();
        if ($n < 25) {
            throw ValidationException::withMessages([
                'groupFicha_id' => "El tema tiene {$n} fichas activas; se necesitan al menos 25.",
            ]);
        }

        // Cota superior de combinaciones únicas: C(n, 25).
        $maxUnique = $this->binomialCapped($n, 25, $count + 1);
        if ($maxUnique < $count) {
            throw ValidationException::withMessages([
                'count' => "Con {$n} fichas solo existen ~{$maxUnique} cartones únicos; pides {$count}.",
            ]);
        }

        $fichaIds   = $fichas->pluck('id')->all();
        $fichaNames = $fichas->pluck('name', 'id')->all();

        $seen  = [];
        $rows  = [];
        $now   = now();
        $start = $now;

        // Intentos máximos globales para evitar bucles si el espacio se agota.
        $maxAttempts = $count * 50;
        $attempts    = 0;

        while (count($rows) < $count) {
            if (++$attempts > $maxAttempts) {
                throw ValidationException::withMessages([
                    'count' => 'No se pudieron generar suficientes cartones únicos (espacio de combinaciones insuficiente).',
                ]);
            }

            // shuffle sobre una copia → 25 fichas sin repetir.
            $pool = $fichaIds;
            shuffle($pool);
            $pick = array_slice($pool, 0, 25);

            // Firma canónica: set de 25 fichas, sin importar orden.
            $signature = $pick;
            sort($signature);
            $sigKey = implode('|', $signature);

            if (isset($seen[$sigKey])) {
                continue;
            }
            $seen[$sigKey] = true;

            $rows[] = $this->buildCardRow($pick, $fichaNames, $groupFichaId, $start, $now);
        }

        $created = DB::transaction(function () use ($rows) {
            Card::insert($rows); // bulk insert

            // Necesitamos los IDs recién insertados. Como insert() no devuelve IDs,
            // usamos el rango contiguo de auto_increment: obtenemos el último ID
            // y calculamos hacia atrás.
            $lastId = (int) DB::table('cards')->max('id');
            $firstId = $lastId - count($rows) + 1;

            $pivot = [];
            $ts = now();
            foreach ($rows as $i => $row) {
                $cardId = $firstId + $i;
                foreach (range(1, 25) as $pos) {
                    $pivot[] = [
                        'card_id'    => $cardId,
                        'ficha_id'   => $row["pos{$pos}"],
                        'posicion'   => $pos,
                        'created_at' => $ts,
                        'updated_at' => $ts,
                    ];
                }
            }
            DB::table('card_ficha')->insert($pivot);

            return array_map(fn ($row, $i) => [
                'id'            => $firstId + $i,
                'name'          => $row['name'],
                'groupFicha_id' => $row['groupFicha_id'],
            ], $rows, array_keys($rows));
        });

        return ['created' => count($created), 'cards' => $created];
    }

    /** Construye una fila lista para insert en `cards`. */
    private function buildCardRow(array $pick, array $fichaNames, int $groupFichaId, $start, $now): array
    {
        $row = [
            'name'          => null,
            'groupFicha_id' => $groupFichaId,
            'active'        => '1',
            'start_date'    => $start,
            'end_date'      => null,
            'created_at'    => $now,
            'updated_at'    => $now,
        ];

        // pos01..pos25 + desc_pos01..desc_pos25
        for ($i = 0; $i < 25; $i++) {
            $num = str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
            $row["pos{$num}"]      = $pick[$i];
            $row["desc_pos{$num}"] = $fichaNames[$pick[$i]] ?? '';
        }

        // comb01..comb14 + desc_comb01..desc_comb14
        foreach (self::COMBINATIONS as $key => $idx) {
            $ids   = array_map(fn ($i) => $pick[$i], $idx);
            $names = array_map(fn ($i) => $fichaNames[$pick[$i]] ?? '', $idx);
            $row[$key]              = implode('|', $ids);
            $row['desc_' . $key]    = implode('|', $names);
        }

        // combTotal + desc_combTotal
        $row['combTotal']      = implode('|', $pick);
        $row['desc_combTotal'] = implode('|', array_map(fn ($id) => $fichaNames[$id] ?? '', $pick));

        return $row;
    }

    /** C(n,k) con tope para evitar overflow. */
    private function binomialCapped(int $n, int $k, int $cap): int
    {
        if ($k > $n) return 0;
        $k = min($k, $n - $k);
        $result = 1;
        for ($i = 1; $i <= $k; $i++) {
            $result = intdiv($result * ($n - $k + $i), $i);
            if ($result >= $cap) return $cap;
        }
        return $result;
    }
}