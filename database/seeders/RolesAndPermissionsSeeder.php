<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions based on policy naming convention
        $permissions = [
            // Role permissions
            'view_any_role',
            'view_role',
            'create_role',
            'update_role',
            'delete_role',
            'delete_any_role',
            'force_delete_role',
            'force_delete_any_role',
            'restore_role',
            'restore_any_role',
            'replicate_role',
            'reorder_role',
            
            // Customer permissions
            'view_any_customer',
            'view_customer',
            'create_customer',
            'update_customer',
            'delete_customer',
            'delete_any_customer',
            
            // Scooter permissions
            'view_any_scooter',
            'view_scooter',
            'create_scooter',
            'update_scooter',
            'delete_scooter',
            'delete_any_scooter',
            
            // ServiceOrder permissions
            'view_any_service_order',
            'view_service_order',
            'create_service_order',
            'update_service_order',
            'delete_service_order',
            'delete_any_service_order',
            'change_status_service_order',
            
            // SparePart permissions
            'view_any_spare_part',
            'view_spare_part',
            'create_spare_part',
            'update_spare_part',
            'delete_spare_part',
            'delete_any_spare_part',
            'manage_inventory',
            
            // Payment permissions
            'view_any_payment',
            'view_payment',
            'create_payment',
            'update_payment',
            'delete_payment',
            'delete_any_payment',
            
            // Dashboard permissions
            'view_dashboard',
            'view_reports'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        // Admin role
        $adminRole = Role::create(['name' => 'administrator']);
        $adminRole->givePermissionTo(Permission::all());

        // Technician role
        $technicianRole = Role::create(['name' => 'technician']);
        $technicianRole->givePermissionTo([
            'view_any_customer',
            'view_customer',
            'view_any_scooter',
            'view_scooter',
            'view_any_service_order',
            'view_service_order',
            'update_service_order',
            'change_status_service_order',
            'view_any_spare_part',
            'view_spare_part',
            'view_dashboard',
        ]);

        // Create an admin user
        $adminUser = User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $adminUser->assignRole('administrator');

        // Create a technician user
        $technicianUser = User::create([
            'name' => 'Technician',
            'email' => 'tech@example.com',
            'password' => Hash::make('password'),
        ]);
        $technicianUser->assignRole('technician');
    }
}
