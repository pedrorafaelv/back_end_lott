<?php

namespace Database\Factories;

use App\Models\Via;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Via>
 */
class ViaFactory extends Factory
{
    protected $model = Via::class;

    /**
     * Definición por defecto (datos aleatorios coherentes).
     * Se usa cuando NO aplicas ningún state.
     */
    public function definition(): array
    {
        return [
            'code'          => $this->faker->unique()->slug(2),
            'label'         => $this->faker->words(2, true),
            'icon'          => 'fas fa-circle',
            'color'         => $this->faker->hexColor(),
            'type'          => $this->faker->randomElement(['credit', 'debit', 'neutral']),
            'is_active'     => true,
            'display_order' => $this->faker->numberBetween(1, 100),
        ];
    }

    // ==================================================================
    //  STATES PARA CADA VÍA DEL CATÁLOGO
    // ==================================================================

    // -------------------- ENTRADAS (credit) --------------------

    public function deposit(): static
    {
        return $this->state(fn () => [
            'code'          => 'deposit',
            'label'         => 'Depósito',
            'icon'          => 'fas fa-arrow-down',
            'color'         => '#00e676',
            'type'          => 'credit',
            'is_active'     => true,
            'display_order' => 1,
        ]);
    }

    public function award(): static
    {
        return $this->state(fn () => [
            'code'          => 'award',
            'label'         => 'Premio',
            'icon'          => 'fas fa-trophy',
            'color'         => '#ffd700',
            'type'          => 'credit',
            'is_active'     => true,
            'display_order' => 2,
        ]);
    }

    public function bonus(): static
    {
        return $this->state(fn () => [
            'code'          => 'bonus',
            'label'         => 'Regalía',
            'icon'          => 'fas fa-gift',
            'color'         => '#00e676',
            'type'          => 'credit',
            'is_active'     => true,
            'display_order' => 3,
        ]);
    }

    public function promotion(): static
    {
        return $this->state(fn () => [
            'code'          => 'promotion',
            'label'         => 'Promoción',
            'icon'          => 'fas fa-bullhorn',
            'color'         => '#9b59b6',
            'type'          => 'credit',
            'is_active'     => true,
            'display_order' => 4,
        ]);
    }

    public function refund(): static
    {
        return $this->state(fn () => [
            'code'          => 'refund',
            'label'         => 'Reembolso',
            'icon'          => 'fas fa-undo',
            'color'         => '#3498db',
            'type'          => 'credit',
            'is_active'     => true,
            'display_order' => 5,
        ]);
    }

    public function unblock(): static
    {
        return $this->state(fn () => [
            'code'          => 'unblock',
            'label'         => 'Desbloqueo',
            'icon'          => 'fas fa-unlock',
            'color'         => '#1abc9c',
            'type'          => 'credit',
            'is_active'     => true,
            'display_order' => 6,
        ]);
    }

    // -------------------- SALIDAS (debit) --------------------

    public function withdrawal(): static
    {
        return $this->state(fn () => [
            'code'          => 'withdrawal',
            'label'         => 'Retiro',
            'icon'          => 'fas fa-arrow-up',
            'color'         => '#ff6b6b',
            'type'          => 'debit',
            'is_active'     => true,
            'display_order' => 7,
        ]);
    }

    public function bet(): static
    {
        return $this->state(fn () => [
            'code'          => 'bet',
            'label'         => 'Apuesta',
            'icon'          => 'fas fa-dice',
            'color'         => '#ff4757',
            'type'          => 'debit',
            'is_active'     => true,
            'display_order' => 8,
        ]);
    }

    public function commission(): static
    {
        return $this->state(fn () => [
            'code'          => 'commission',
            'label'         => 'Comisión',
            'icon'          => 'fas fa-percent',
            'color'         => '#f39c12',
            'type'          => 'debit',
            'is_active'     => true,
            'display_order' => 9,
        ]);
    }

    public function block(): static
    {
        return $this->state(fn () => [
            'code'          => 'block',
            'label'         => 'Bloqueo',
            'icon'          => 'fas fa-lock',
            'color'         => '#e74c3c',
            'type'          => 'debit',
            'is_active'     => true,
            'display_order' => 10,
        ]);
    }

    // -------------------- NEUTRALES --------------------

    public function transfer(): static
    {
        return $this->state(fn () => [
            'code'          => 'transfer_send',
            'label'         => 'Transferencia Enviada',
            'icon'          => 'fas fa-exchange-alt',
            'color'         => '#3a7ebf',
            'type'          => 'debit',
            'is_active'     => true,
            'display_order' => 11,
        ]);
    }

    public function mobilePayment(): static
    {
        return $this->state(fn () => [
            'code'          => 'mobile_payment',
            'label'         => 'Pago Móvil',
            'icon'          => 'fas fa-mobile-alt',
            'color'         => '#2ecc71',
            'type'          => 'credit',
            'is_active'     => true,
            'display_order' => 12,
        ]);
    }

    public function adjustment(): static
    {
        return $this->state(fn () => [
            'code'          => 'adjustment',
            'label'         => 'Ajuste',
            'icon'          => 'fas fa-sliders-h',
            'color'         => '#95a5a6',
            'type'          => 'neutral',
            'is_active'     => true,
            'display_order' => 13,
        ]);
    }

     public function transferRecived(): static
    {
        return $this->state(fn () => [
            'code'          => 'Transfer_recived',
            'label'         => 'Transferencia Recibida',
            'icon'          => 'fas fa-circle',
            'color'         => '#909090',
            'type'          => 'credit',
            'is_active'     => true,
            'display_order' => 14,
        ]);
    }


    public function other(): static
    {
        return $this->state(fn () => [
            'code'          => 'other',
            'label'         => 'Otro',
            'icon'          => 'fas fa-circle',
            'color'         => '#909090',
            'type'          => 'neutral',
            'is_active'     => true,
            'display_order' => 14,
        ]);
    }

    // ==================================================================
    //  HELPERS EXTRA
    // ==================================================================

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    /**
     * Devuelve un array con todos los states del catálogo.
     * Útil para iterar en el seeder sin repetir código.
     */
    public static function catalogStates(): array
    {
        return [
            'deposit', 'award', 'bonus', 'promotion', 'refund', 'unblock',
            'withdrawal', 'bet', 'commission', 'block',
            'transfer', 'mobilePayment', 'adjustment', 'other',
        ];
    }
}