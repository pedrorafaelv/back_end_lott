<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LevelUserSeeder extends Seeder
{
    public function run(): void
    {
        // Traemos niveles indexados por slug (más seguro que por id)
        $levels = Level::whereIn('slug', ['novato', 'jugador', 'avanzado', 'elite', 'vip', 'admin'])
            ->get()
            ->keyBy('slug');

        if ($levels->isEmpty()) {
            $this->command->error('No hay niveles. Ejecuta primero LevelSeeder.');
            return;
        }

        $order = ['novato', 'jugador', 'avanzado', 'elite', 'vip'];
        $now = Carbon::now();

        User::query()->chunkById(100, function ($users) use ($levels, $order, $now) {
            foreach ($users as $user) {

                // =============================
                // 1) ADMIN: asignación directa
                // =============================
                if ($user->is_admin && isset($levels['admin'])) {
                    DB::table('level_user')->updateOrInsert(
                        ['user_id' => $user->id, 'is_current' => 1],
                        [
                            'level_id'       => $levels['admin']->id,
                            'games_played'   => 0,
                            'wins'           => 0,
                            'active_raffles' => 0,
                            'total_raffles'  => 0,
                            'total_prizes'   => 0,
                            'assigned_at'    => $now,
                            'notes'          => 'Admin asignado automáticamente por seeder',
                            'created_at'     => $now,
                            'updated_at'     => $now,
                        ]
                    );
                    continue;
                }

                // =============================
                // 2) Datos aleatorios coherentes
                // =============================
                $gamesPlayed = random_int(0, 1500);
                $wins        = $gamesPlayed > 0 ? random_int(0, (int) floor($gamesPlayed * 0.6)) : 0;

                // Días registrado: si tiene created_at, calculamos; si no, null
                $daysRegistered = $user->created_at
                    ? $user->created_at->diffInDays($now)
                    : null;

                // =============================
                // 3) Cálculo del nivel (lógica OR)
                // =============================
                $assignedSlug = 'novato'; // fallback

                foreach ($order as $slug) {
                    $lvl = $levels[$slug] ?? null;
                    if (!$lvl) continue;

                    $condGames = $gamesPlayed   >= $lvl->min_games_played;
                    $condDays  = $daysRegistered !== null
                                    ? $daysRegistered >= $lvl->min_days_registered
                                    : false; // si no hay fecha, esa condición no cuenta
                    $condWins  = $wins          >= $lvl->min_wins;

                    // Si no hay created_at, evaluamos solo games y wins
                    $cumple = $daysRegistered === null
                        ? ($condGames || $condWins)
                        : ($condGames || $condDays || $condWins);

                    if ($cumple) {
                        $assignedSlug = $slug;
                    }
                }

                $assignedLevel = $levels[$assignedSlug];

                // =============================
                // 4) Valores aleatorios extras
                // =============================
                $activeRaffles = random_int(0, (int) $assignedLevel->max_raffles_active);
                $totalRaffles  = random_int($activeRaffles, $activeRaffles + 200);
                $totalPrizes   = round(random_int(0, 50000) + (random_int(0, 99) / 100), 2);

                // =============================
                // 5) Asegurar solo un is_current=1
                // =============================
                DB::table('level_user')
                    ->where('user_id', $user->id)
                    ->update(['is_current' => 0, 'updated_at' => $now]);

                // =============================
                // 6) Insert / Update del nivel actual
                // =============================
                DB::table('level_user')->updateOrInsert(
                    ['user_id' => $user->id, 'is_current' => 1],
                    [
                        'level_id'       => $assignedLevel->id,
                        'games_played'   => $gamesPlayed,
                        'wins'           => $wins,
                        'active_raffles' => $activeRaffles,
                        'total_raffles'  => $totalRaffles,
                        'total_prizes'   => $totalPrizes,
                        'assigned_at'    => $now,
                        'notes'          => 'Asignado automáticamente por seeder',
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ]
                );
            }
        });

        $this->command->info('✅ level_user poblado correctamente.');
    }
}