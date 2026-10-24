<?php namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateBots extends Command
{
    protected $signature   = 'raffle:create-bots {count=10}';
    protected $description = 'Crea usuarios bot para simular jugadores';

    public function handle(): int
    {
        $count = (int) $this->argument('count');
        $created = 0;

        for ($i = 0; $i < $count; $i++) {
            $name = 'bot_' . Str::random(8);
            User::create([
                'name'     => $name,
                'email'    => "{$name}@bots.local",
                'password' => Hash::make(Str::random(32)),
                'is_bot'   => true,
            ]);
            $created++;
        }

        $this->info("✅ {$created} bots creados.");
        return self::SUCCESS;
    }
}