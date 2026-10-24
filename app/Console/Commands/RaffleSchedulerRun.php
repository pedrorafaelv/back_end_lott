<?php
namespace App\Console\Commands;

use App\Services\RaffleSchedulerService;
use Illuminate\Console\Command;

class RaffleSchedulerRun extends Command
{
    protected $signature   = 'raffle:scheduler-run';
    protected $description = 'Crea/activa sorteos automáticos según el scheduler';

    public function handle(RaffleSchedulerService $svc): int
    {
        $this->line(json_encode($svc->process()));
        return self::SUCCESS;
    }
}