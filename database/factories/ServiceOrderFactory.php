<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Scooter;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServiceOrder>
 */
class ServiceOrderFactory extends Factory
{
    protected $model = ServiceOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $receivedAt = $this->faker->dateTimeBetween('-3 months', 'now');
        $status = $this->faker->randomElement(['pending', 'in_progress', 'completed', 'cancelled']);
        
        // If status is completed, set a completion date
        $completedAt = ($status === 'completed') 
            ? $this->faker->dateTimeBetween($receivedAt, 'now') 
            : null;
            
        $laborHours = $this->faker->randomFloat(1, 0.5, 5);
        $price = $laborHours * 50 + $this->faker->randomFloat(2, 10, 200);
        
        $problemDescriptions = [
            'Not charging properly',
            'Battery draining too fast',
            'Motor making strange noise',
            'Brakes not working properly',
            'Display not functioning',
            'Throttle sticking',
            'Flat tire needs replacement',
            'Loose handlebar',
            'Water damage',
            'Controller issues',
        ];
        
        return [
            'order_number' => 'SO-' . $this->faker->unique()->numerify('######'),
            'customer_id' => Customer::factory(),
            'scooter_id' => function (array $attributes) {
                return Scooter::factory()->for(Customer::find($attributes['customer_id']));
            },
            'received_at' => $receivedAt,
            'completed_at' => $completedAt,
            'status' => $status,
            'problem_description' => $this->faker->randomElement($problemDescriptions),
            'work_performed' => $status === 'completed' ? $this->faker->paragraph() : null,
            'labor_hours' => $laborHours,
            'price' => $price,
            'technician_name' => $this->faker->optional(0.7)->name(),
            'assigned_to' => $this->faker->optional(0.8)->randomElement(User::role('technician')->pluck('id')->toArray()),
        ];
    }

    /**
     * Indicate that the service order is pending.
     */
    public function pending(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'completed_at' => null,
            'work_performed' => null,
        ]);
    }

    /**
     * Indicate that the service order is in progress.
     */
    public function inProgress(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the service order is completed.
     */
    public function completed(): Factory
    {
        return $this->state(function (array $attributes) {
            $receivedAt = $attributes['received_at'] ?? $this->faker->dateTimeBetween('-3 months', '-1 day');
            
            return [
                'status' => 'completed',
                'received_at' => $receivedAt,
                'completed_at' => $this->faker->dateTimeBetween($receivedAt, 'now'),
                'work_performed' => $this->faker->paragraph(),
            ];
        });
    }
}