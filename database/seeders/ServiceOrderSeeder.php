<?php

namespace Database\Seeders;

use App\Models\Scooter;
use App\Models\ServiceOrder;
use App\Models\SparePart;
use Illuminate\Database\Seeder;

class ServiceOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create service orders for random scooters
        $scooters = Scooter::all();
        foreach ($scooters as $scooter) {
            if (rand(0, 1)) {
                ServiceOrder::factory()->create([
                    'customer_id' => $scooter->customer_id,
                    'scooter_id' => $scooter->id,
                ]);
            }
        }
        
        // Create orders with specific statuses
        ServiceOrder::factory(5)->pending()->create();
        ServiceOrder::factory(3)->inProgress()->create();
        ServiceOrder::factory(7)->completed()->create();
        
        // Assign spare parts to service orders
        $this->assignSparePartsToOrders();
    }
    
    /**
     * Assign spare parts to existing service orders
     */
    private function assignSparePartsToOrders(): void
    {
        $serviceOrders = ServiceOrder::all();
        $spareParts = SparePart::where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->get();
        
        if ($spareParts->isEmpty()) {
            return;
        }
        
        foreach ($serviceOrders as $serviceOrder) {
            // Randomly attach spare parts to about half of the orders
            if (rand(0, 1)) {
                $usedParts = $spareParts->random(rand(1, 3));
                foreach ($usedParts as $part) {
                    $quantity = rand(1, 2);
                    $serviceOrder->spareParts()->attach($part->id, [
                        'quantity' => $quantity,
                        'price_per_unit' => $part->selling_price,
                    ]);
                    
                    // Update the service order price to include parts
                    $partsCost = $quantity * $part->selling_price;
                    $serviceOrder->price += $partsCost;
                    $serviceOrder->save();
                    
                    // Reduce stock quantity
                    $part->stock_quantity = max(0, $part->stock_quantity - $quantity);
                    $part->save();
                }
            }
        }
    }
}
