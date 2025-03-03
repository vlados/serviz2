<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_order_id' => \App\Models\ServiceOrder::factory(),
            'amount' => fake()->randomFloat(2, 10, 500),
            'payment_method' => fake()->randomElement(['cash', 'card', 'bank_transfer', 'other']),
            'reference_number' => fake()->boolean(70) ? null : fake()->numerify('REF-#####'),
            'payment_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'notes' => fake()->boolean(70) ? null : fake()->sentence(),
            'recorded_by' => \App\Models\User::factory(),
        ];
    }
}
