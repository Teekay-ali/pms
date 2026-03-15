<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions grouped by domain
        $permissions = [
            // Projects
            'projects.view',
            'projects.create',
            'projects.update',
            'projects.delete',
            'projects.assign',

            // Tasks
            'tasks.view',
            'tasks.create',
            'tasks.update',
            'tasks.delete',
            'tasks.assign',

            // Budgets
            'budgets.view',
            'budgets.create',
            'budgets.update',
            'budgets.approve',

            // Expenses
            'expenses.view',
            'expenses.create',
            'expenses.update',
            'expenses.approve',

            // Resources / Inventory
            'resources.view',
            'resources.create',
            'resources.update',
            'resources.delete',

            // Vendors & Contractors
            'vendors.view',
            'vendors.create',
            'vendors.update',
            'vendors.delete',

            // HR / Users
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'roles.assign',

            // Change Orders
            'change_orders.view',
            'change_orders.create',
            'change_orders.update',
            'change_orders.delete',
            'change_orders.approve',

            // Reports
            'reports.view',
            'reports.export',

            // System
            'settings.manage',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Define roles and their permissions
        $roles = [
            'admin' => $permissions, // all permissions

            'ceo' => [
                'projects.view',
                'tasks.view',
                'budgets.view',
                'expenses.view',
                'resources.view',
                'vendors.view',
                'users.view',
                'reports.view',
                'reports.export',
                'change_orders.view',
                'change_orders.approve',
            ],

            'finance' => [
                'projects.view',
                'budgets.view',
                'budgets.create',
                'budgets.update',
                'budgets.approve',
                'expenses.view',
                'expenses.approve',
                'reports.view',
                'reports.export',
                'change_orders.view',
                'change_orders.approve',
            ],

            'accountant' => [
                'projects.view',
                'budgets.view',
                'expenses.view',
                'expenses.create',
                'expenses.update',
                'reports.view',
                'reports.export',
                'change_orders.view',
            ],

            'architect' => [
                'projects.view',
                'tasks.view',
                'tasks.update',
                'resources.view',
                'change_orders.view',
            ],

            'project_manager' => [
                'projects.view',
                'projects.create',
                'projects.update',
                'projects.assign',
                'tasks.view',
                'tasks.create',
                'tasks.update',
                'tasks.delete',
                'tasks.assign',
                'budgets.view',
                'expenses.view',
                'resources.view',
                'resources.create',
                'resources.update',
                'vendors.view',
                'reports.view',
                'change_orders.view',
                'change_orders.create',
                'change_orders.update',
                'change_orders.delete',
                'change_orders.approve',
            ],

            'hr' => [
                'users.view',
                'users.create',
                'users.update',
                'projects.view',
                'reports.view',
            ],

            'store_manager' => [
                'resources.view',
                'resources.create',
                'resources.update',
                'resources.delete',
                'vendors.view',
                'projects.view',
            ],

            'contractor' => [
                'tasks.view',
                'tasks.update',
                'projects.view',
            ],

            'vendor' => [
                'vendors.view',
                'projects.view',
            ],

            'site_worker' => [
                'tasks.view',
                'tasks.update',
            ],
        ];

        // Create roles and assign permissions
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }
    }
}
