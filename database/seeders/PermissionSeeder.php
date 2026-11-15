<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions
        $permissions = [
            // Dashboard
            'view dashboard',
            
            // POS
            'access pos',
            'make sales',
            
            // Products
            'view products',
            'create products',
            'edit products',
            'delete products',
            
            // Categories
            'view categories',
            'manage categories',
            
            // Employees
            'view employees',
            'manage employees',
            
            // Customers/Members
            'view customers',
            'manage customers',
            
            // Suppliers
            'view suppliers',
            'manage suppliers',
            
            // Promotions
            'view promotions',
            'manage promotions',
            
            // Reports
            'view reports',
            'export reports',
            
            // Attendance
            'view attendance',
            'manage attendance',
            
            // Roles/Positions
            'view roles',
            'manage roles',
            
            // Settings
            'view settings',
            'manage settings',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Update Admin role - give all permissions
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->syncPermissions(Permission::all());
        }

        // Update Staff role - give basic permissions
        $staffRole = Role::where('name', 'staff')->first();
        if ($staffRole) {
            $staffRole->syncPermissions([
                'view dashboard',
                'access pos',
                'make sales',
                'view products',
                'view customers',
                'view reports',
            ]);
        }

        $this->command->info('Permissions created and assigned successfully!');
        $this->command->info('Staff can now: view dashboard, access POS, make sales, view products, view customers, view reports');
        $this->command->info('Admin has all permissions');
    }
}
