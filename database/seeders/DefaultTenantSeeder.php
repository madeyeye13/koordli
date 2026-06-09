<?php

namespace Database\Seeders;

use App\Models\Tenant\EventType;
use App\Models\Tenant\TenantEventStatus;
use App\Models\Tenant\TenantTaskCategory;
use App\Models\Tenant\TenantLabel;
use App\Models\Tenant\VendorCategory;
use Illuminate\Database\Seeder;

class DefaultTenantSeeder extends Seeder
{
    public function run(int $tenantId): void
    {
        $this->seedEventTypes($tenantId);
        $this->seedEventStatuses($tenantId);
        $this->seedTaskCategories($tenantId);
        $this->seedVendorCategories($tenantId);
        $this->seedLabels($tenantId);
    }

    private function seedEventTypes(int $tenantId): void
    {
        $types = [
            ['name' => 'Wedding',          'icon' => 'rings',      'color' => '#7C3AED'],
            ['name' => 'Birthday',         'icon' => 'cake',       'color' => '#F59E0B'],
            ['name' => 'Corporate Event',  'icon' => 'briefcase',  'color' => '#3B82F6'],
            ['name' => 'Concert',          'icon' => 'music',      'color' => '#EF4444'],
            ['name' => 'Private Event',    'icon' => 'star',       'color' => '#10B981'],
            ['name' => 'Social Event',     'icon' => 'users',      'color' => '#F97316'],
            ['name' => 'Conference',       'icon' => 'mic',        'color' => '#6366F1'],
        ];

        foreach ($types as $i => $type) {
            EventType::firstOrCreate(
                ['tenant_id' => $tenantId, 'name' => $type['name']],
                [...$type, 'tenant_id' => $tenantId, 'sort_order' => $i, 'is_active' => true]
            );
        }
    }

    private function seedEventStatuses(int $tenantId): void
    {
        $statuses = [
            ['name' => 'Inquiry',     'color' => '#F59E0B', 'is_default' => true,  'sort_order' => 0],
            ['name' => 'Planning',    'color' => '#3B82F6', 'is_default' => false, 'sort_order' => 1],
            ['name' => 'Confirmed',   'color' => '#7C3AED', 'is_default' => false, 'sort_order' => 2],
            ['name' => 'In Progress', 'color' => '#10B981', 'is_default' => false, 'sort_order' => 3],
            ['name' => 'Completed',   'color' => '#1C1917', 'is_default' => false, 'sort_order' => 4],
            ['name' => 'Archived',    'color' => '#78716C', 'is_default' => false, 'sort_order' => 5],
        ];

        foreach ($statuses as $status) {
            TenantEventStatus::firstOrCreate(
                ['tenant_id' => $tenantId, 'name' => $status['name']],
                [...$status, 'tenant_id' => $tenantId]
            );
        }
    }

    private function seedTaskCategories(int $tenantId): void
    {
        $categories = [
            ['name' => 'Pre-Event',    'color' => '#3B82F6', 'icon' => 'calendar',  'sort_order' => 0],
            ['name' => 'Logistics',    'color' => '#F59E0B', 'icon' => 'truck',      'sort_order' => 1],
            ['name' => 'On The Day',   'color' => '#10B981', 'icon' => 'clock',      'sort_order' => 2],
            ['name' => 'Post-Event',   'color' => '#78716C', 'icon' => 'check',      'sort_order' => 3],
            ['name' => 'Client',       'color' => '#7C3AED', 'icon' => 'user',       'sort_order' => 4],
            ['name' => 'Admin',        'color' => '#EF4444', 'icon' => 'folder',     'sort_order' => 5],
        ];

        foreach ($categories as $category) {
            TenantTaskCategory::firstOrCreate(
                ['tenant_id' => $tenantId, 'name' => $category['name']],
                [...$category, 'tenant_id' => $tenantId]
            );
        }
    }

    private function seedVendorCategories(int $tenantId): void
    {
        $categories = [
            ['name' => 'Venue',           'icon' => 'building',   'sort_order' => 0],
            ['name' => 'Catering',        'icon' => 'utensils',   'sort_order' => 1],
            ['name' => 'Photography',     'icon' => 'camera',     'sort_order' => 2],
            ['name' => 'Videography',     'icon' => 'video',      'sort_order' => 3],
            ['name' => 'Decoration',      'icon' => 'flower',     'sort_order' => 4],
            ['name' => 'Entertainment',   'icon' => 'music',      'sort_order' => 5],
            ['name' => 'Beauty',          'icon' => 'sparkles',   'sort_order' => 6],
            ['name' => 'Transport',       'icon' => 'car',        'sort_order' => 7],
            ['name' => 'Security',        'icon' => 'shield',     'sort_order' => 8],
            ['name' => 'Ushers',          'icon' => 'users',      'sort_order' => 9],
            ['name' => 'Special Effects', 'icon' => 'zap',        'sort_order' => 10],
            ['name' => 'Other',           'icon' => 'more',       'sort_order' => 11],
        ];

        foreach ($categories as $category) {
            VendorCategory::firstOrCreate(
                ['tenant_id' => $tenantId, 'name' => $category['name']],
                [...$category, 'tenant_id' => $tenantId]
            );
        }
    }

    private function seedLabels(int $tenantId): void
    {
        $labels = [
            ['name' => 'VIP',            'color' => '#F59E0B', 'entity_type' => 'guest'],
            ['name' => 'Urgent',         'color' => '#EF4444', 'entity_type' => 'task'],
            ['name' => 'Follow Up',      'color' => '#3B82F6', 'entity_type' => 'task'],
            ['name' => 'Pending Review', 'color' => '#7C3AED', 'entity_type' => 'event'],
            ['name' => 'High Priority',  'color' => '#EF4444', 'entity_type' => 'vendor'],
        ];

        foreach ($labels as $label) {
            TenantLabel::firstOrCreate(
                ['tenant_id' => $tenantId, 'name' => $label['name'], 'entity_type' => $label['entity_type']],
                [...$label, 'tenant_id' => $tenantId]
            );
        }
    }
}