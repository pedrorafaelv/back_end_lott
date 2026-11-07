<?php
namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastSeen
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            if (!$user->is_bot) {
                // Normaliza a Carbon por si el modelo no castea la columna
                $last = $user->last_seen_at
                    ? Carbon::parse($user->last_seen_at)
                    : null;

            $last = $user->last_seen_at ? \Carbon\Carbon::parse($user->last_seen_at) : null;
            if (!$last || $last->lt(now()->subSeconds(30)))  {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['last_seen_at' => now()]);
                }
            }
        }
        return $next($request);
    }
}