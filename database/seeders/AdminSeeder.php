<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Créer le compte administrateur par défaut.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@tangier-sports.com'],
            [
                'name'              => 'Admin',
                'email'             => 'admin@tangier-sports.com',
                'password'          => Hash::make('password'),
                'role'              => 'admin',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅ Compte admin créé : admin@tangier-sports.com / password');
    }
}
