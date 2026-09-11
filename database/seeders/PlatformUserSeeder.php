<?php

namespace Database\Seeders;

use App\Models\Central\PlatformUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class PlatformUserSeeder extends Seeder
{
    public function run(): void
    {
        // Platform-level roles use tenant_id = 0 as a sentinel (real
        // tenant IDs start from 1) — see the migration and
        // PermissionSeeder comments explaining why NULL isn't possible
        // (model_has_roles.tenant_id is part of a composite primary
        // key). Each seeder command is its own process, so this must
        // be set here too, not just inside PermissionSeeder.
        app(PermissionRegistrar::class)->setPermissionsTeamId(0);

        $admin = PlatformUser::firstOrCreate(
            ['email' => 'admin@koordli.com'],
            [
                'uuid'     => Str::uuid(),
                'name'     => 'Koordli Admin',
                'email'    => 'admin@koordli.com',
                'password' => Hash::make('Koordli@Admin2026'),
                'role'     => 'platform_owner', // kept for backward compatibility only
            ]
        );

        if (!$admin->hasRole('platform_owner')) {
            $admin->assignRole('platform_owner');
        }

        $this->command->info('Platform user seeded. Email: admin@koordli.com');
    }
}
