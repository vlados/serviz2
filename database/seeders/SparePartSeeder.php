<?php

namespace Database\Seeders;

use App\Models\SparePart;
use Illuminate\Database\Seeder;

class SparePartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create different types of spare parts with various stock levels
        SparePart::factory(14)->create(); // Regular stock
        SparePart::factory(3)->lowStock()->create(); // Low stock (1-5 units)
        SparePart::factory(1)->outOfStock()->create(); // Out of stock (0 units)
        SparePart::factory(2)->inactive()->create(); // Inactive parts
    }
}
