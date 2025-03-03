<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Scooter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Scooter>
 */
class ScooterFactory extends Factory
{
    protected $model = Scooter::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $scooterModels = [
            'Xiaomi Pro 2',
            'Segway Ninebot Max',
            'Kaabo Mantis',
            'Apollo City',
            'Inokim OX',
            'Dualtron Eagle',
            'Zero 10X',
            'Emove Cruiser',
        ];

        return [
            'model' => $this->faker->randomElement($scooterModels),
            'serial_number' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{6}'),
            'customer_id' => Customer::factory(),
            'status' => $this->faker->randomElement(['in_use', 'in_repair', 'not_working']),
            'max_speed' => $this->faker->numberBetween(25, 45),
            'battery_capacity' => $this->faker->numberBetween(8000, 20000),
            'weight' => $this->faker->randomFloat(1, 12, 30),
            'specifications' => null,
        ];
    }

    /**
     * Indicate that the scooter is in use.
     */
    public function inUse(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_use',
        ]);
    }

    /**
     * Indicate that the scooter is in repair.
     */
    public function inRepair(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_repair',
        ]);
    }

    /**
     * Indicate that the scooter is not working.
     */
    public function notWorking(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'not_working',
        ]);
    }
}