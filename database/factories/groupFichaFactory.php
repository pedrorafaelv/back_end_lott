<?php

namespace Database\Factories;
use App\Models\GroupFicha;                             

use Illuminate\Database\Eloquent\Factories\Factory;


class GroupFichaFactory extends Factory
{
    protected $model = GroupFicha::class;

    public function definition()
    {
        return [
            'name' => $this->faker->unique()->word() . ' Group',
            'description' => $this->faker->sentence(),
            'status' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function caricatura()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Caricaturas theme',
                'description' => 'Tema de figuras estilo caricatura',
                'status' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });
    }

    public function comics()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Comics theme',
                'description' => 'Tema de comics y superhéroes',
                'status' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });
    }
    
    public function futbol()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Futbol theme',
                'description' => 'Tema de fútbol y deportes',
                'status' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });
    }

    public function animales()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Animales theme',
                'description' => 'Tema de animales y naturaleza',
                'status' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });
    }

     public function dinosaurus()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Dinosaurus theme',
                'description' => 'Tema de dinosaurus y prehistoria',
                'status' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });
    }

    public function deportes()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Deportes theme',
                'description' => 'Tema de deportes y actividades físicas',
                'status' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });
    }

    public function videojuegos()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Videojuegos theme',
                'description' => 'Tema de videojuegos y entretenimiento digital',
                'status' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });
    }

    public function cine()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Cine theme',
                'description' => 'Tema de películas y cine',
                'status' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });
    }

    public function flags()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Flags theme',
                'description' => 'Tema de banderas y países',
                'status' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });
    }

    function history()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'History theme',
                'description' => 'Tema de historia y eventos históricos',
                'status' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });
    }

    function terror_movies()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Terror Movies theme',
                'description' => 'Tema de películas de terror y suspenso',
                'status' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });
    }
}

        



        

