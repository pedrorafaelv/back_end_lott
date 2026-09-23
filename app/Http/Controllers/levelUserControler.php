<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Level;
use App\Models\Raffle;

class LevelUserController extends Controller
{
    /**
     * GET /api/user/level
     * Devuelve el nivel actual + stats del usuario
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $level = $user->active_level;

        if (!$level) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo determinar el nivel del usuario',
            ], 404);
        }

        // Stats en tiempo real
        $stats = [
            'active_raffles' => Raffle::where('user_id', $user->id)
                ->where('status', 'active')
                ->count(),
            'total_raffles' => Raffle::where('user_id', $user->id)->count(),
            'games_played' => $user->games_played ?? 0,
            'wins' => $user->wins ?? 0,
            'total_prizes' => $user->total_prizes ?? 0,
        ];

        return response()->json([
            'success' => true,
            'level' => [
                'id' => $level->id,
                'slug' => $level->slug,
                'name' => $level->name,
                'description' => $level->description,
                'icon' => $level->icon,
                'color' => $level->color,
                'max_raffles_active' => $level->max_raffles_active,
                'max_amount' => (float) $level->max_amount,
                'max_retention_percent' => $level->max_retention_percent,
                'can_create_private' => $level->can_create_private,
                'can_use_auto_type' => $level->can_use_auto_type,
                'can_use_custom_fichas' => $level->can_use_custom_fichas,
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * GET /api/user/level/permissions
     * Devuelve solo los permisos calculados
     */
    public function permissions(Request $request)
    {
        $user = $request->user();
        $level = $user->active_level;

        if (!$level) {
            return response()->json(['success' => false], 404);
        }

        $activeRaffles = Raffle::where('user_id', $user->id)
            ->where('status', 'active')
            ->count();

        return response()->json([
            'success' => true,
            'can_create_raffle' => $level->max_raffles_active > $activeRaffles,
            'max_amount' => (float) $level->max_amount,
            'max_retention_percent' => $level->max_retention_percent,
            'can_create_private' => $level->can_create_private,
            'can_use_auto_type' => $level->can_use_auto_type,
            'can_use_custom_fichas' => $level->can_use_custom_fichas,
            'active_raffles' => $activeRaffles,
            'max_raffles_active' => $level->max_raffles_active,
        ]);
    }
}