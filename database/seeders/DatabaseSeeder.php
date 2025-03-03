<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // First call the roles and permissions seeder
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);
        
        // Seed demo data if needed
        if (app()->environment('local', 'development')) {
            $this->command->info('Seeding demo data...');
            
            $this->call([
                CustomerSeeder::class,
                SparePartSeeder::class,
                ScooterSeeder::class,
                ServiceOrderSeeder::class,
            ]);
            
            $this->command->info('Demo data seeded successfully!');
        }
    }
}
