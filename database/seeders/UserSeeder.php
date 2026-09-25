<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::findOrCreate('Admin', 'web');
        $staffRole = Role::findOrCreate('Staff', 'web');

        $admin = User::firstOrCreate(
            ['email' => 'admin@notaris.app'],
            ['name' => 'Administrator', 'password' => Hash::make('Admin@12345')],
        );
        $admin->syncRoles([$adminRole]);

        $staff = User::firstOrCreate(
            ['email' => 'staff@notaris.app'],
            ['name' => 'Staff Notaris', 'password' => Hash::make('Staff@12345')],
        );
        $staff->syncRoles([$staffRole]);
    }
}
