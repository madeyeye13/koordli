<?php

namespace Database\Seeders;

use App\Models\Central\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            ['key' => 'task_assigned', 'category' => 'tasks', 'subject' => 'New task assigned: {{task_name}}', 'body' => "Hi {{user_name}},\n\nYou've been assigned a new task: \"{{task_name}}\" for {{event_name}}.\nDue: {{due_date}}"],
            ['key' => 'task_due_soon', 'category' => 'tasks', 'subject' => 'Task due soon: {{task_name}}', 'body' => "Hi {{user_name}},\n\nYour task \"{{task_name}}\" for {{event_name}} is due {{remaining_time}}.\nDue date: {{due_date}}"],
            ['key' => 'task_overdue', 'category' => 'tasks', 'subject' => 'Overdue task: {{task_name}}', 'body' => "Hi {{user_name}},\n\nYour task \"{{task_name}}\" for {{event_name}} was due {{due_date}} and is now overdue. Please update its status as soon as possible."],
            ['key' => 'contract_awaiting_signature', 'category' => 'contracts', 'subject' => 'Contract still awaiting signature', 'body' => "Hi {{user_name}},\n\nThe contract \"{{task_name}}\" sent to {{event_name}} is still awaiting signature."],
            ['key' => 'contract_fully_signed', 'category' => 'contracts', 'subject' => 'Contract fully signed: {{event_name}}', 'body' => "Hi {{user_name}},\n\nGreat news — the contract with {{task_name}} is now fully signed by both parties."],
            ['key' => 'invoice_due_soon', 'category' => 'invoices', 'subject' => 'Invoice due soon: {{task_name}}', 'body' => "Hi {{user_name}},\n\nInvoice {{event_name}} from {{task_name}} is due {{due_date}}."],
            ['key' => 'invoice_overdue', 'category' => 'invoices', 'subject' => 'Overdue invoice: {{task_name}}', 'body' => "Hi {{user_name}},\n\nInvoice {{event_name}} from {{task_name}} was due {{due_date}} and is now overdue."],
            ['key' => 'invoice_fully_paid', 'category' => 'invoices', 'subject' => 'Invoice fully paid: {{event_name}}', 'body' => "Hi {{user_name}},\n\nInvoice {{event_name}} from {{task_name}} has been fully paid."],
            ['key' => 'booking_submitted', 'category' => 'bookings', 'subject' => 'New booking submission: {{task_name}}', 'body' => "Hi {{user_name}},\n\n{{task_name}} just submitted {{event_name}}."],
            ['key' => 'rsvp_submitted', 'category' => 'rsvp', 'subject' => 'New RSVP: {{task_name}}', 'body' => "Hi {{user_name}},\n\n{{task_name}} just responded to {{event_name}}."],
            ['key' => 'vendor_application_submitted', 'category' => 'vendors', 'subject' => 'New vendor application: {{task_name}}', 'body' => "Hi {{user_name}},\n\n{{task_name}} has applied to join your vendor network."],
            ['key' => 'vendor_application_pending', 'category' => 'vendors', 'subject' => 'Vendor application awaiting review: {{task_name}}', 'body' => "Hi {{user_name}},\n\n{{task_name}}'s application has been pending for over 48 hours."],
            ['key' => 'runsheet_item_starting_soon', 'category' => 'runsheets', 'subject' => 'Starting soon: {{task_name}}', 'body' => "Hi {{user_name}},\n\n\"{{task_name}}\" for {{event_name}} starts {{remaining_time}}."],
            ['key' => 'runsheet_dependency_delayed', 'category' => 'runsheets', 'subject' => 'Dependency delayed: {{task_name}}', 'body' => "Hi {{user_name}},\n\nYour item \"{{task_name}}\" depends on \"{{event_name}}\", which has just been marked delayed. Your start time may shift."],
            ['key' => 'conversation_mention', 'category' => 'conversations', 'subject' => 'You were mentioned by {{task_name}}', 'body' => "Hi {{user_name}},\n\n{{task_name}} mentioned you in a conversation for {{event_name}}."],
            ['key' => 'conversation_unread_digest', 'category' => 'conversations', 'subject' => 'Unread messages in {{task_name}}', 'body' => "Hi {{user_name}},\n\nYou have unread messages in \"{{task_name}}\" for {{event_name}}."],
            ['key' => 'conversation_added', 'category' => 'conversations', 'subject' => 'Added to {{task_name}}', 'body' => "Hi {{user_name}},\n\n{{added_by}} added you to \"{{task_name}}\" for {{event_name}}."],
            ['key' => 'document_shared_via_link', 'category' => 'documents', 'subject' => 'New file uploaded: {{file_name}}', 'body' => "A guest uploaded \"{{file_name}}\" to the media library for {{event_name}} using your upload link."],
            ['key' => 'client_suggested_vendor', 'category' => 'vendors', 'subject' => 'New vendor suggestion for {{event_name}}', 'body' => "{{user_name}}, your client {{action_phrase}} {{event_name}}: \"{{vendor_name}}\". Review it in your suggestions inbox."],
            ['key' => 'client_vendor_decision', 'category' => 'vendors', 'subject' => 'Client responded to your vendor suggestion', 'body' => "{{user_name}}, your client {{action_phrase}} {{event_name}}: \"{{vendor_name}}\"."],
            ['key' => 'vendor_suggestion_received', 'category' => 'vendors', 'subject' => 'A vendor has been suggested for {{event_name}}', 'body' => "Hi {{user_name}}, \"{{vendor_name}}\" has been suggested for your event, {{event_name}}. Review it in your Vendors page."],
            ['key' => 'vendor_suggestion_decided', 'category' => 'vendors', 'subject' => 'Update on your vendor suggestion', 'body' => "Hi {{user_name}}, your suggestion \"{{vendor_name}}\" for {{event_name}} was {{decision}}."],
            // NOTE: fixed a real typo from the source data — {{event-name}} → {{event_name}}, matching every other template's convention
            ['key' => 'client_task_milestone_completed', 'category' => 'event_milestones', 'subject' => 'Update on {{event_name}}', 'body' => "Hi {{user_name}}, \"{{task_name}}\" has been completed for {{event_name}}."],
            ['key' => 'client_vendor_booking_status_changed', 'category' => 'vendor_bookings', 'subject' => 'Vendor update for {{event_name}}', 'body' => "Hi {{user_name}}, {{vendor_name}}'s booking status is now {{status}} for {{event_name}}."],
            ['key' => 'client_payment_recorded', 'category' => 'payments', 'subject' => 'Payment received — {{event_name}}', 'body' => "Hi {{user_name}}, we've recorded your payment of {{amount}} toward {{event_name}}."],
            ['key' => 'client_event_details_changed', 'category' => 'event_details', 'subject' => 'Update to {{event_name}}', 'body' => "Hi {{user_name}}, the following was updated for {{event_name}}: {{changed}}."],
            ['key' => 'client_rsvp_deadline_approaching', 'category' => 'rsvp', 'subject' => 'RSVP reminder — {{event_name}}', 'body' => "Hi {{user_name}}, your RSVP deadline for {{event_name}} is {{due_date}} ({{remaining_time}})."],
            ['key' => 'vendor_task_assigned', 'category' => 'tasks', 'subject' => 'New task: {{task_name}}', 'body' => "Hi {{user_name}}, \"{{task_name}}\" has been assigned to you for {{event_name}} (due {{due_date}})."],
            ['key' => 'vendor_marked_delayed', 'category' => 'runsheets', 'subject' => 'Vendor delay reported', 'body' => "Hi {{user_name}}, a vendor marked \"{{task_name}}\" as delayed for {{event_name}}."],
            ['key' => 'vendor_booking_created', 'category' => 'bookings', 'subject' => "You've been booked", 'body' => "Hi {{user_name}}, you've been booked for {{event_name}}."],
            ['key' => 'vendor_booking_status_changed', 'category' => 'bookings', 'subject' => 'Booking status update', 'body' => "Hi {{user_name}}, your booking status for {{event_name}} is now {{status}}."],
            ['key' => 'vendor_contract_sent', 'category' => 'contracts', 'subject' => 'Contract ready for signature', 'body' => "Hi {{user_name}}, \"{{contract_name}}\" has been sent to you for signature."],
            ['key' => 'client_rsvp_milestone_reached', 'category' => 'rsvp', 'subject' => 'RSVP update — {{event_name}}', 'body' => "Hi {{user_name}}, {{percentage}}% of your expected guests have responded for {{event_name}}."],
            ['key' => 'quick_access_link_regenerated', 'category' => 'security', 'subject' => 'Your quick access link was regenerated', 'body' => "{{initiator}} regenerated your quick access link. Your previous link no longer works."],
            ['key' => 'moodboard_client_approved', 'category' => 'moodboards', 'subject' => 'Moodboard approved — {{event_name}}', 'body' => "Hi {{user_name}}, {{client_name}} approved \"{{board_title}}\" for {{event_name}}."],
            ['key' => 'moodboard_client_changes_requested', 'category' => 'moodboards', 'subject' => 'Changes requested — {{event_name}}', 'body' => "Hi {{user_name}}, {{client_name}} requested changes to \"{{board_title}}\": {{note}}"],
            ['key' => 'client_moodboard_shared', 'category' => 'moodboards', 'subject' => 'A moodboard was shared with you — {{event_name}}', 'body' => "Hi {{user_name}}, \"{{board_title}}\" has been shared with you for {{event_name}}."],
            ['key' => 'planner_fee_fully_collected', 'category' => 'budget', 'subject' => 'Your fee is fully paid — {{event_name}}', 'body' => "Hi {{user_name}}, your professional fee of {{amount}} for {{event_name}} has been fully collected."],
        ];

        foreach ($templates as $t) {
            NotificationTemplate::updateOrCreate(
                ['key' => $t['key']],
                [...$t, 'is_active' => true]
            );
        }

        $this->command->info('Notification templates seeded: ' . count($templates));
    }
}