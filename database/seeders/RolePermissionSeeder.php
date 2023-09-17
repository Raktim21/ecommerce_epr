<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'get-role-list',
            'get-role-info',
            'get-permission-list',
            'update-kpi-lookup',
            'create-role',
            'update-role',
            'delete-role',
            'assign-role-to-user',
            'get-user-list',
            'get-user-info',
            'create-user',
            'update-user',
            'delete-user',
            'create-user-employee-profile',
            'employee-payable-salary-list',
            'store-salary',
            'get-point-type-list',
            'update-point-type',
            'get-user-point-data',
            'get-transport-allowance',
            'export-transport-allowance',
            'create-update-transport-allowance',
            'change-transport-allowance-status',
            'transport-allowance-payment-slip',
            'get-food-allowance',
            'food-allowance-payment-slip',
            'export-food-allowance',
            'create-delete-food-allowance',
            'change-food-allowance-status',
            'get-service',
            'create-service',
            'update-service',
            'get-client-list',
            'export-client-list',
            'get-unpaid-client-list',
            'get-client-info',
            'get-client-gps-data',
            'create-client',
            'import-client',
            'update-client',
            'delete-client',
            'get-client-follow-up',
            'create-client-follow-up',
            'create-client-follow-up-reminder',
            'update-client-follow-up',
            'delete-client-follow-up',
            'get-payment-type',
            'create-client-payment',
            'get-payslip',
            'get-bills',
            'create-bill',
            'assign-tasks-to-users',
            'get-todo-statuses',
            'get-todo-list',
            'update-todo-list',
            'update-task-documents',
            'delete-tasks'
        ];

        foreach ($permissions as $value)
        {
            Permission::create([
                'name' => $value,
                'guard_name' => 'api'
            ]);
        }

        $role = Role::create([
            'name' => 'Super Admin',
            'guard_name' => 'api'
        ]);

        $role->givePermissionTo(Permission::all());

        $user = User::where('email', 'admin@admin.com')->first();

        $user->assignRole($role);
    }
}
