<?php

declare(strict_types=1);

use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

return [

    'tenant_model' => \App\Models\Central\Tenant::class,

    'id_generator' => Stancl\Tenancy\UUIDGenerator::class,

    'central_domains' => [
        '127.0.0.1',
        'localhost',
    ],

    'bootstrappers' => [
        Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper::class,
    ],

    'database' => [
        'based_on_tenant' => false,
        'prefix' => '',
        'suffix' => '',
        'managers' => [
            'sqlite' => Stancl\Tenancy\Database\Managers\SQLiteDatabaseManager::class,
            'mysql'  => Stancl\Tenancy\Database\Managers\MySQLDatabaseManager::class,
            'pgsql'  => Stancl\Tenancy\Database\Managers\PostgreSQLDatabaseManager::class,
        ],
    ],

    'cache' => [
        'tag_base' => 'tenant',
    ],

    'filesystem' => [
        'suffix_base' => 'tenant',
        'disks' => [
            'local',
            'public',
        ],
        'root_override' => [
            'local'  => '%storage_path%/app/',
            'public' => '%storage_path%/app/public/',
        ],
    ],

    'features' => [],

    'migration_parameters' => [
        '--force' => true,
    ],

    'seeder_parameters' => [
        '--force' => true,
    ],

];