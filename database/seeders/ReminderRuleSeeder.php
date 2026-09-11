<?php

namespace Database\Seeders;

use App\Models\Central\ReminderRule;
use Illuminate\Database\Seeder;

class ReminderRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            ['key' => 'task_due_soon', 'category' => 'tasks', 'notification_type' => 'task_due_soon', 'pattern' => 'once', 'offsets' => [-1440, -120], 'escalate_after_hours' => null, 'escalate_to' => null, 'priority' => 'high', 'template_key' => 'task_due_soon'],
            ['key' => 'task_overdue', 'category' => 'tasks', 'notification_type' => 'task_overdue', 'pattern' => 'every_6h', 'offsets' => [0], 'escalate_after_hours' => 24, 'escalate_to' => 'tenant_owner', 'priority' => 'critical', 'template_key' => 'task_overdue'],
            ['key' => 'contract_awaiting_signature', 'category' => 'contracts', 'notification_type' => 'contract_awaiting_signature', 'pattern' => 'once', 'offsets' => [4320, 10080], 'escalate_after_hours' => null, 'escalate_to' => null, 'priority' => 'high', 'template_key' => 'contract_awaiting_signature'],
            ['key' => 'invoice_due_soon', 'category' => 'invoices', 'notification_type' => 'invoice_due_soon', 'pattern' => 'once', 'offsets' => [-4320], 'escalate_after_hours' => null, 'escalate_to' => null, 'priority' => 'high', 'template_key' => 'invoice_due_soon'],
            ['key' => 'invoice_overdue', 'category' => 'invoices', 'notification_type' => 'invoice_overdue', 'pattern' => 'daily', 'offsets' => [0], 'escalate_after_hours' => 72, 'escalate_to' => 'tenant_owner', 'priority' => 'critical', 'template_key' => 'invoice_overdue'],
            ['key' => 'vendor_application_pending', 'category' => 'vendors', 'notification_type' => 'vendor_application_pending', 'pattern' => 'once', 'offsets' => [2880], 'escalate_after_hours' => null, 'escalate_to' => null, 'priority' => 'normal', 'template_key' => 'vendor_application_pending'],
            ['key' => 'runsheet_item_starting_soon', 'category' => 'runsheets', 'notification_type' => 'runsheet_item_starting_soon', 'pattern' => 'once', 'offsets' => [-30], 'escalate_after_hours' => null, 'escalate_to' => null, 'priority' => 'high', 'template_key' => 'runsheet_item_starting_soon'],
            ['key' => 'rsvp_deadline_approaching', 'category' => 'rsvp', 'notification_type' => 'rsvp_deadline_approaching', 'pattern' => 'once', 'offsets' => [-1440, -120], 'escalate_after_hours' => null, 'escalate_to' => null, 'priority' => 'normal', 'template_key' => 'client_rsvp_deadline_approaching'],
        ];

        foreach ($rules as $r) {
            ReminderRule::updateOrCreate(
                ['key' => $r['key']],
                [...$r, 'custom_interval_minutes' => null, 'is_active' => true]
            );
        }

        $this->command->info('Reminder rules seeded: ' . count($rules));
    }
}