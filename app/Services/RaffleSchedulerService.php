<?php
namespace App\Services;

use App\Models\Raffle;
use App\Models\SchedulerSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RaffleSchedulerService
{
    // ── Claves de configuración ──
    public const KEY_ACTIVE         = 'raffle_scheduler_active';
    public const KEY_MIN_PLAYERS    = 'raffle_scheduler_min_players';
    public const KEY_AUTO_PLAY      = 'raffle_scheduler_auto_play';
    public const KEY_AUTO_PLAY_RATE = 'raffle_scheduler_auto_play_rate'; // seg entre draws
    public const KEY_PLATFORM_USER  = 'raffle_scheduler_platform_user';
    public const KEY_TEMPLATE_ID    = 'raffle_scheduler_template_raffle_id';

    // ── Lock keys ──
    public const LOCK_PROCESS = 'raffle_scheduler_process_lock';

    // ═══════════════════════════════════════════
    // ESTADO PÚBLICO
    // ═══════════════════════════════════════════

    public function getStatus(): array
    {
        $raffle = $this->currentRaffle();
        $players = $raffle ? $this->countDistinctPlayers($raffle->id) : 0;
        $minPlayers = (int) SchedulerSetting::get(self::KEY_MIN_PLAYERS, 5);

        return [
            'enabled'         => (bool) SchedulerSetting::get(self::KEY_ACTIVE, false),
            'auto_play'       => (bool) SchedulerSetting::get(self::KEY_AUTO_PLAY, false),
            'auto_play_rate'  => (int) SchedulerSetting::get(self::KEY_AUTO_PLAY_RATE, 3),
            'min_players'     => $minPlayers,
            'current_raffle'  => $raffle ? [
                'id'                    => $raffle->id,
                'name'                  => $raffle->name,
                'state'                 => $this->raffleState($raffle),
                'players_count'         => $players,
                'players_needed'        => max(0, $minPlayers - $players),
                'start_date'            => $raffle->start_date,
                'min_players_reached_at'=> $raffle->min_players_reached_at,
                'end_date'              => $raffle->end_date,
                'winner'                => $raffle->winner,
                'full_winner'           => $raffle->full_winner,
            ] : null,
            'bots_count'      => User::where('is_bot', true)->count(),
        ];
    }

    public function toggle(): bool
    {
        $new = !(bool) SchedulerSetting::get(self::KEY_ACTIVE, false);
        SchedulerSetting::set(self::KEY_ACTIVE, $new ? 1 : 0);
        Log::info('[RaffleScheduler] toggled', ['active' => $new]);
        return $new;
    }

    public function setAutoPlay(bool $on): void
    {
        SchedulerSetting::set(self::KEY_AUTO_PLAY, $on ? 1 : 0);
    }

    public function setMinPlayers(int $n): void
    {
        SchedulerSetting::set(self::KEY_MIN_PLAYERS, max(2, $n));
    }

    public function setAutoPlayRate(int $sec): void
    {
        SchedulerSetting::set(self::KEY_AUTO_PLAY_RATE, max(1, $sec));
    }

    public function setTemplateRaffleId(int $id): void
    {
        SchedulerSetting::set(self::KEY_TEMPLATE_ID, $id);
    }

    // ═══════════════════════════════════════════
    // CICLO PRINCIPAL (cron)
    // ═══════════════════════════════════════════

    /**
     * Ejecutado por `raffle:scheduler-run` cada minuto.
     * 🔒 Usa lock para evitar que dos procesos lo corran a la vez.
     */
    public function process(): array
    {
        if (!(bool) SchedulerSetting::get(self::KEY_ACTIVE, false)) {
            return ['success' => true, 'action' => 'skip', 'reason' => 'scheduler_disabled'];
        }

        // 🔒 Lock distribuido: si otro proceso lo está corriendo, salir
        $lock = Cache::lock(self::LOCK_PROCESS, 30);
        if (!$lock->get()) {
            return ['success' => true, 'action' => 'skip', 'reason' => 'locked_by_another_process'];
        }

        try {
            return $this->doProcess();
        } finally {
            $lock->release();
        }
    }

    private function doProcess(): array
    {
        // 🔒 Todo en una transacción para atomicidad
        return DB::transaction(function () {
            // Bloquea todas las filas relevantes
            $active = Raffle::whereNull('end_date')
                ->lockForUpdate()
                ->get();

            // Caso 1: sin raffle abierto → crear uno WAITING
            if ($active->isEmpty()) {
                $raffle = $this->createWaitingRaffle();
                return ['success' => true, 'action' => 'created', 'raffle_id' => $raffle->id];
            }

            // Debería haber solo uno; si hay varios, log de alerta
            if ($active->count() > 1) {
                Log::warning('[RaffleScheduler] Multiple active raffles', ['ids' => $active->pluck('id')]);
            }

            $raffle = $active->first();

            // Caso 2: raffle WAITING → verificar si ya hay min_players
            if (!$raffle->start_date) {
                $activated = $this->tryActivate($raffle);
                return ['success' => true,
                        'action' => $activated ? 'activated' : 'waiting',
                        'raffle_id' => $raffle->id,
                        'players' => $this->countDistinctPlayers($raffle->id)];
            }

            // Caso 3: raffle ACTIVE → si auto_play está on, dispatch bot job
            if ($raffle->start_date && !$raffle->end_date) {
                if ((bool) SchedulerSetting::get(self::KEY_AUTO_PLAY, false)) {
                    \App\Jobs\BotDrawJob::dispatch($raffle->id)
                        ->delay(now()->addSeconds(2));
                    return ['success' => true, 'action' => 'bot_dispatched', 'raffle_id' => $raffle->id];
                }
                return ['success' => true, 'action' => 'active_no_autoplay', 'raffle_id' => $raffle->id];
            }

            return ['success' => true, 'action' => 'noop'];
        });
    }

    // ═══════════════════════════════════════════
    // ACTIVACIÓN (WAITING → ACTIVE)
    // ═══════════════════════════════════════════

    /**
     * 🔒 Llamado también DESDE putCard (después de cada compra)
     * para que el raffle se active en cuanto llegue el jugador #5,
     * sin esperar al cron del minuto.
     */
    public function tryActivate(Raffle $raffle): bool
    {
        $minPlayers = (int) SchedulerSetting::get(self::KEY_MIN_PLAYERS, 5);
        $players = $this->countDistinctPlayers($raffle->id);

        if ($players >= $minPlayers && !$raffle->start_date) {
            $raffle->start_date = now();
            $raffle->min_players_reached_at = now();
            $raffle->save();
            Log::info('[RaffleScheduler] Activated', ['raffle_id' => $raffle->id, 'players' => $players]);
            return true;
        }
        return false;
    }

    public function countDistinctPlayers(int $raffleId): int
    {
        return (int) DB::table('card_raffle')
            ->where('raffle_id', $raffleId)
            ->whereNotNull('user_id')
            ->distinct()
            ->count('user_id');
    }

    // ═══════════════════════════════════════════
    // CREACIÓN DEL RAFFLE WAITING
    // ═══════════════════════════════════════════

    private function createWaitingRaffle(): Raffle
    {
        $platformUserId = (int) SchedulerSetting::get(self::KEY_PLATFORM_USER, env('PLATFORM_USER_ID', 1));

        // Buscar template: raffle configurado explícitamente o último del platform user
        $templateId = (int) SchedulerSetting::get(self::KEY_TEMPLATE_ID, 0);
        $template = $templateId > 0
            ? Raffle::find($templateId)
            : Raffle::where('user_id', $platformUserId)->orderByDesc('id')->first();

        if (!$template) {
            throw new \RuntimeException(
                'No template raffle available. Configura raffle_scheduler_template_raffle_id ' .
                'o crea un raffle de referencia con user_id=' . $platformUserId
            );
        }

        $new = $template->replicate([
            'id', 'created_at', 'updated_at',
            'start_date', 'end_date', 'winner', 'full_winner',
            'min_players_reached_at',
        ]);
        $new->user_id    = $platformUserId;
        $new->name       = $this->uniqueName($template->name);
        $new->start_date = null;              // 🔑 WAITING
        $new->end_date   = null;
        $new->min_players_reached_at = null;
        $new->winner     = null;
        $new->full_winner= null;
        $new->scheduled_date = now();
        $new->save();

        Log::info('[RaffleScheduler] Created waiting raffle', ['raffle_id' => $new->id]);
        return $new;
    }

    private function uniqueName(string $base): string
    {
        do {
            $name = $base . ' · ' . now()->format('YmdHi') . ' · ' . Str::random(4);
        } while (Raffle::where('name', $name)->exists());
        return $name;
    }

    // ═══════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════

    public function currentRaffle(): ?Raffle
    {
        return Raffle::whereNull('end_date')->latest('id')->first();
    }

    public function raffleState(Raffle $r): string
    {
        if ($r->end_date)   return 'FINISHED';
        if ($r->start_date) return 'ACTIVE';
        return 'WAITING';
    }
}