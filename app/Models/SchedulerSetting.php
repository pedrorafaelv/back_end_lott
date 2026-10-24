<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchedulerSetting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Obtiene un valor por key
     */
    public static function get(string $key, $default = null)
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    /**
     * Guarda un valor (lo crea o lo actualiza)
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
    }

    /**
     * Devuelve TODAS las settings como array asociativo [key => value].
     * ⚠️ Renombrado: antes se llamaba all() y choca con Eloquent.
     */
    public static function getAllAsArray(): array
    {
        return static::pluck('value', 'key')->toArray();
    }
}