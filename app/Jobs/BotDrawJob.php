<?php namespace App\Jobs;

use App\Models\Raffle;
use App\Models\SchedulerSetting;
use App\Models\User;
use App\Services\RaffleSchedulerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BotDrawJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 15;

    public function __construct(public int $raffleId) {}

    public function handle(RaffleSchedulerService $svc): void
    {
        // 🔒 Lock por raffle: solo un draw a la vez
        $lock = Cache::lock("raffle_draw_lock_{$this->raffleId}", 5);
        if (!$lock->get()) {
            return;
        }

        try {
            $raffle = Raffle::find($this->raffleId);
            if (!$raffle || $raffle->end_date || !$raffle->start_date) return;

            if (!(bool) SchedulerSetting::get('raffle_scheduler_auto_play', false)) return;

            // 1) Elegir un bot (aleatorio)
            $bot = User::where('is_bot', true)->inRandomOrder()->first();
            if (!$bot) {
                Log::warning('[BotDrawJob] No bot users available');
                return;
            }

            // 2) Elegir un cartón disponible del raffle
            $card = DB::table('card_raffle')
                ->where('raffle_id', $raffle->id)
                ->whereNull('user_id')
                ->inRandomOrder()
                ->first();
            if (!$card) return;

            // 3) Asignar cartón al bot (sin saldo real — los bots no gastan)
            DB::table('card_raffle')
                ->where('id', $card->id)
                ->update(['user_id' => $bot->id, 'status' => 'active', 'updated_at' => now()]);

            // 4) Ejecutar un draw (usar el mismo endpoint getNewRecord internamente)
            //    Reutilizamos el controlador pasándole un Request sintético
            $request = new \Illuminate\Http\Request([
                'raffle_id' => $raffle->id,
                'user_id'   => $bot->id,
            ]);

            app(\App\Http\Controllers\Api\RaffleController::class)
                ->getNewRecord($request);
        } finally {
            $lock->release();
        }

        // 5) Re-dispatch con delay configurable (backoff natural)
        $rateSec = (int) SchedulerSetting::get('raffle_scheduler_auto_play_rate', 3);
        $stillActive = Raffle::where('id', $this->raffleId)
            ->whereNull('end_date')
            ->whereNotNull('start_date')
            ->exists();

        if ($stillActive && (bool) SchedulerSetting::get('raffle_scheduler_auto_play', false)) {
            self::dispatch($this->raffleId)->delay(now()->addSeconds($rateSec));
        }
    }
}