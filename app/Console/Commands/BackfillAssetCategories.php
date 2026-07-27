<?php

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use App\Models\Tenant\AssetCategory;
use Illuminate\Console\Command;

class BackfillAssetCategories extends Command
{
    protected $signature   = 'koordli:backfill-asset-categories';
    protected $description = 'One-time backfill: seed default asset categories for tenants created before the Assets module existed';

    public function handle(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            if (AssetCategory::where('tenant_id', $tenant->id)->exists()) {
                continue; // already has categories, skip
            }

            $categories = [
                ['name' => 'Cameras',      'icon' => 'camera',   'sort_order' => 0],
                ['name' => 'Audio',        'icon' => 'mic',      'sort_order' => 1],
                ['name' => 'Lighting',     'icon' => 'zap',      'sort_order' => 2],
                ['name' => 'LED Screens',  'icon' => 'tv',       'sort_order' => 3],
                ['name' => 'Furniture',    'icon' => 'chair',    'sort_order' => 4],
                ['name' => 'Decor',        'icon' => 'flower',   'sort_order' => 5],
                ['name' => 'Other',        'icon' => 'more',     'sort_order' => 6],
            ];

            foreach ($categories as $category) {
                AssetCategory::create([...$category, 'tenant_id' => $tenant->id]);
            }

            $this->info("Seeded asset categories for: {$tenant->name}");
        }
    }
}