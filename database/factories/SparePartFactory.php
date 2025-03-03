<?php

namespace Database\Factories;

use App\Models\SparePart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SparePart>
 */
class SparePartFactory extends Factory
{
    protected $model = SparePart::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $parts = [
            'Front Wheel' => ['SP-WHEEL-F', 'Replacement front wheel for electric scooters.'],
            'Rear Wheel' => ['SP-WHEEL-R', 'Replacement rear wheel for electric scooters.'],
            'Inner Tube' => ['SP-TUBE', 'Inner tube for scooter tires.'],
            'Battery Pack' => ['SP-BATT', 'Replacement battery pack for electric scooters.'],
            'Controller' => ['SP-CTRL', 'Electronic controller for motor and battery management.'],
            'Brake Pads' => ['SP-BRAKE', 'Replacement brake pads for disc brakes.'],
            'Handlebar' => ['SP-HNDL', 'Replacement handlebar assembly.'],
            'Throttle' => ['SP-THRTL', 'Throttle control mechanism.'],
            'Display' => ['SP-DISP', 'LCD display unit for speed and battery indicators.'],
            'Fender' => ['SP-FNDR', 'Front and rear fenders to prevent splashes.'],
            'LED Lights' => ['SP-LIGHT', 'Headlight and taillight LED assemblies.'],
            'Folding Mechanism' => ['SP-FOLD', 'Folding joint parts for portable scooters.'],
            'Charger' => ['SP-CHRG', 'Power adapter for charging the scooter battery.'],
            'Motor' => ['SP-MOTOR', 'Electric motor for scooter propulsion.'],
        ];
        
        $name = $this->faker->randomElement(array_keys($parts));
        $partInfo = $parts[$name];
        
        $purchasePrice = $this->faker->randomFloat(2, 10, 200);
        $sellingPrice = $purchasePrice * 1.4; // 40% markup
        
        return [
            'name' => $name,
            'part_number' => $partInfo[0] . '-' . $this->faker->unique()->numerify('####'),
            'description' => $partInfo[1],
            'stock_quantity' => $this->faker->numberBetween(0, 50),
            'purchase_price' => $purchasePrice,
            'selling_price' => $sellingPrice,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the spare part is low in stock.
     */
    public function lowStock(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => $this->faker->numberBetween(0, 5),
        ]);
    }

    /**
     * Indicate that the spare part is out of stock.
     */
    public function outOfStock(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
        ]);
    }

    /**
     * Indicate that the spare part is inactive.
     */
    public function inactive(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}