<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@sitepro.com'],
            [
                'name'                => 'Super Admin',
                'password'            => Hash::make('Admin@9519!'),
                'must_change_password' => true,
                'email_verified_at'   => now(),
            ]
        );

        $admin->syncRoles('admin');

        $this->command->info('Admin user created: admin@sitepro.com / Admin@9519!');
    }
}
