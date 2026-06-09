<?php

namespace Database\Seeders;

use App\Models\Central\PlatformUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PlatformUserSeeder extends Seeder
{
    public function run(): void
    {
        PlatformUser::firstOrCreate(
            ['email' => 'admin@koordli.com'],
            [
                'uuid'     => Str::uuid(),
                'name'     => 'Koordli Admin',
                'email'    => 'admin@koordli.com',
                'password' => Hash::make('Koordli@Admin2026'),
                'role'     => 'platform_owner',
            ]
        );

        $this->command->info('Platform user seeded. Email: admin@koordli.com');
    }
}