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

        // Create permissions
        $permissions = [
            // Customer permissions
            'преглед на клиенти',
            'създаване на клиенти',
            'редактиране на клиенти',
            'изтриване на клиенти',
            
            // Scooter permissions
            'преглед на тротинетки',
            'създаване на тротинетки',
            'редактиране на тротинетки',
            'изтриване на тротинетки',
            
            // Service Order permissions
            'преглед на поръчки',
            'създаване на поръчки',
            'редактиране на поръчки',
            'изтриване на поръчки',
            'промяна на статус',
            
            // Spare Part permissions
            'преглед на части',
            'създаване на части',
            'редактиране на части',
            'изтриване на части',
            'управление на склад',
            
            // Dashboard permissions
            'преглед на табло',
            'преглед на справки'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        // Admin role
        $adminRole = Role::create(['name' => 'администратор']);
        $adminRole->givePermissionTo(Permission::all());

        // Technician role
        $technicianRole = Role::create(['name' => 'техник']);
        $technicianRole->givePermissionTo([
            'преглед на клиенти',
            'преглед на тротинетки',
            'преглед на поръчки',
            'редактиране на поръчки',
            'промяна на статус',
            'преглед на части',
            'преглед на табло',
        ]);

        // Create an admin user
        $adminUser = User::create([
            'name' => 'Администратор',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $adminUser->assignRole('администратор');

        // Create a technician user
        $technicianUser = User::create([
            'name' => 'Техник',
            'email' => 'tech@example.com',
            'password' => Hash::make('password'),
        ]);
        $technicianUser->assignRole('техник');
    }
}
