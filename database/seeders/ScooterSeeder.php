<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Scooter;
use Illuminate\Database\Seeder;

class ScooterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 1-3 scooters for each customer
        $customers = Customer::all();
        foreach ($customers as $customer) {
            Scooter::factory(rand(1, 3))->create([
                'customer_id' => $customer->id,
            ]);
        }

        // Create scooters with specific statuses
        Scooter::factory(5)->inUse()->create([
            'customer_id' => Customer::inRandomOrder()->first()->id,
        ]);
        
        Scooter::factory(3)->inRepair()->create([
            'customer_id' => Customer::inRandomOrder()->first()->id,
        ]);
        
        Scooter::factory(2)->notWorking()->create([
            'customer_id' => Customer::inRandomOrder()->first()->id,
        ]);
    }
}
