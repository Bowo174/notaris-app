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
        $notarisRole = Role::findOrCreate('Notaris', 'web');
        $ppatRole = Role::findOrCreate('PPAT', 'web');

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

        $notaris = User::firstOrCreate(
            ['email' => 'notaris@notaris.app'],
            ['name' => 'User Notaris', 'password' => Hash::make('Notaris@12345')],
        );
        $notaris->syncRoles([$notarisRole]);

        $ppat = User::firstOrCreate(
            ['email' => 'ppat@notaris.app'],
            ['name' => 'User PPAT', 'password' => Hash::make('Ppat@12345')],
        );
        $ppat->syncRoles([$ppatRole]);
    }
}
