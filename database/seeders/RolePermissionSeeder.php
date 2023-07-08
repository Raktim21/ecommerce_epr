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
            'create-role',
            'update-role',
            'delete-role',
            'assign-role-to-user',
            'get-user-list',
            'get-user-info',
            'create-user',
            'update-user',
            'delete-user',
            'get-client-list',
            'export-client-list',
            'get-unpaid-client-list',
            'get-client-info',
            'create-client',
            'import-client',
            'update-client',
            'delete-client',
            'get-client-follow-up',
            'create-client-follow-up',
            'update-client-follow-up',
            'delete-client-follow-up',
            'get-client-payment',
            'create-client-payment',
            'get-payment-type',
            'get-payslip',
            'get-website',
            'create-website',
            'get-payment-category',
            'create-payment-category',
            'delete-payment-category',
            'get-point-type-list',
            'update-point-type',
            'get-user-point-data',
            'get-transport-allowance',
            'export-transport-allowance',
            'create-update-transport-allowance',
            'change-transport-allowance-status',
            'get-food-allowance',
            'export-food-allowance',
            'create-delete-food-allowance',
            'change-food-allowance-status'
        ];

        foreach ($permissions as $value)
        {
            Permission::create([
                'name' => $value,
                'guard_name' => 'api'
            ]);
        }

//        $role = Role::create([
//            'name' => 'Super Admin',
//            'guard_name' => 'api'
//        ]);

        $role = Role::find(1);

        $role->givePermissionTo(Permission::all());

        $user = User::where('email', 'admin@admin.com')->first();

        $user->assignRole($role);
    }
}
