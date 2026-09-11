<?php

namespace Database\Seeders;

use App\Models\Central\IndustryProfile;
use Illuminate\Database\Seeder;

class IndustryProfileSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = [
            [
                'key'  => 'wedding_events',
                'name' => 'Wedding & Events',
                'icon' => '💍',
                'description' => 'For wedding planners and general event coordinators.',
                'terminology' => [],
                'default_event_types' => [
                    ['name' => 'Wedding',         'icon' => 'rings',     'color' => '#7C3AED'],
                    ['name' => 'Engagement',      'icon' => 'heart',     'color' => '#EC4899'],
                    ['name' => 'Birthday',        'icon' => 'cake',      'color' => '#F59E0B'],
                    ['name' => 'Private Event',   'icon' => 'star',      'color' => '#10B981'],
                ],
                'default_vendor_categories' => [
                    ['name' => 'Venue', 'icon' => 'building'], ['name' => 'Catering', 'icon' => 'utensils'],
                    ['name' => 'Photography', 'icon' => 'camera'], ['name' => 'Videography', 'icon' => 'video'],
                    ['name' => 'Decoration', 'icon' => 'flower'], ['name' => 'Entertainment', 'icon' => 'music'],
                    ['name' => 'Beauty', 'icon' => 'sparkles'], ['name' => 'Transport', 'icon' => 'car'],
                ],
                'default_task_categories' => [
                    ['name' => 'Pre-Event', 'color' => '#3B82F6'], ['name' => 'Logistics', 'color' => '#F59E0B'],
                    ['name' => 'On The Day', 'color' => '#10B981'], ['name' => 'Post-Event', 'color' => '#78716C'],
                ],
                'default_roles' => ['Coordinator', 'Finance', 'Operations', 'Social Media Manager'],
                'recommended_feature_flags' => ['rsvp', 'runsheet', 'client_portal', 'booking_forms'],
            ],
            [
                'key'  => 'corporate',
                'name' => 'Corporate Events',
                'icon' => '💼',
                'description' => 'For corporate event and conference organizers.',
                'terminology' => ['guest' => 'Attendee', 'guest_plural' => 'Attendees'],
                'default_event_types' => [
                    ['name' => 'Corporate Event',  'icon' => 'briefcase', 'color' => '#3B82F6'],
                    ['name' => 'Product Launch',   'icon' => 'zap',       'color' => '#7C3AED'],
                    ['name' => 'Team Retreat',      'icon' => 'users',    'color' => '#10B981'],
                    ['name' => 'Award Ceremony',    'icon' => 'award',    'color' => '#F59E0B'],
                ],
                'default_vendor_categories' => [
                    ['name' => 'Venue', 'icon' => 'building'], ['name' => 'Catering', 'icon' => 'utensils'],
                    ['name' => 'AV & Production', 'icon' => 'video'], ['name' => 'Transport', 'icon' => 'car'],
                    ['name' => 'Security', 'icon' => 'shield'],
                ],
                'default_task_categories' => [
                    ['name' => 'Pre-Event', 'color' => '#3B82F6'], ['name' => 'Logistics', 'color' => '#F59E0B'],
                    ['name' => 'On The Day', 'color' => '#10B981'], ['name' => 'Client', 'color' => '#7C3AED'],
                ],
                'default_roles' => ['Event Manager', 'Operations', 'Finance'],
                'recommended_feature_flags' => ['runsheet', 'client_portal', 'booking_forms'],
            ],
            [
                'key'  => 'production',
                'name' => 'Production',
                'icon' => '🎬',
                'description' => 'For film, TV, concert, and stage production companies.',
                'terminology' => [
                    'event' => 'Production', 'client' => 'Producer', 'guest' => 'Audience', 'vendor' => 'Supplier',
                    'event_plural' => 'Productions', 'client_plural' => 'Producers', 'guest_plural' => 'Audience', 'vendor_plural' => 'Suppliers',
                ],
                'default_event_types' => [
                    ['name' => 'Film Shoot',       'icon' => 'video',     'color' => '#EF4444'],
                    ['name' => 'TV Production',    'icon' => 'tv',        'color' => '#3B82F6'],
                    ['name' => 'Concert',          'icon' => 'music',     'color' => '#7C3AED'],
                    ['name' => 'Commercial Shoot',  'icon' => 'camera',    'color' => '#F59E0B'],
                    ['name' => 'Theatre Production','icon' => 'star',     'color' => '#10B981'],
                ],
                'default_vendor_categories' => [
                    ['name' => 'Equipment Rental', 'icon' => 'camera'], ['name' => 'Crew', 'icon' => 'users'],
                    ['name' => 'Catering', 'icon' => 'utensils'], ['name' => 'Location', 'icon' => 'building'],
                    ['name' => 'Security', 'icon' => 'shield'], ['name' => 'Transport', 'icon' => 'car'],
                ],
                'default_task_categories' => [
                    ['name' => 'Pre-Production', 'color' => '#3B82F6'], ['name' => 'Shoot Day', 'color' => '#10B981'],
                    ['name' => 'Post-Production', 'color' => '#78716C'], ['name' => 'Logistics', 'color' => '#F59E0B'],
                ],
                'default_roles' => ['Line Producer', '1st AD', 'Unit Manager', 'Production Coordinator'],
                'recommended_feature_flags' => ['runsheet', 'vendor_portal'],
            ],
            [
                'key'  => 'church',
                'name' => 'Church',
                'icon' => '⛪',
                'description' => 'For church event and production teams.',
                'terminology' => ['guest' => 'Congregation Member', 'client' => 'Ministry Lead', 'guest_plural' => 'Congregation Members', 'client_plural' => 'Ministry Leads'],
                'default_event_types' => [
                    ['name' => 'Sunday Service', 'icon' => 'star',    'color' => '#7C3AED'],
                    ['name' => 'Conference',     'icon' => 'mic',     'color' => '#3B82F6'],
                    ['name' => 'Crusade',        'icon' => 'zap',     'color' => '#F59E0B'],
                    ['name' => 'Wedding',        'icon' => 'rings',   'color' => '#EC4899'],
                ],
                'default_vendor_categories' => [
                    ['name' => 'AV & Sound', 'icon' => 'video'], ['name' => 'Catering', 'icon' => 'utensils'],
                    ['name' => 'Decoration', 'icon' => 'flower'], ['name' => 'Transport', 'icon' => 'car'],
                ],
                'default_task_categories' => [
                    ['name' => 'Pre-Event', 'color' => '#3B82F6'], ['name' => 'On The Day', 'color' => '#10B981'],
                    ['name' => 'Volunteers', 'color' => '#7C3AED'], ['name' => 'Post-Event', 'color' => '#78716C'],
                ],
                'default_roles' => ['Ministry Coordinator', 'Volunteer Lead', 'Finance'],
                'recommended_feature_flags' => ['rsvp', 'runsheet'],
            ],
            [
                'key'  => 'conference',
                'name' => 'Conference',
                'icon' => '🎤',
                'description' => 'For conference and summit organizers.',
                'terminology' => ['guest' => 'Delegate', 'guest_plural' => 'Delegates'],
                'default_event_types' => [
                    ['name' => 'Conference', 'icon' => 'mic',   'color' => '#3B82F6'],
                    ['name' => 'Summit',     'icon' => 'star',  'color' => '#7C3AED'],
                    ['name' => 'Workshop',   'icon' => 'users', 'color' => '#10B981'],
                ],
                'default_vendor_categories' => [
                    ['name' => 'Venue', 'icon' => 'building'], ['name' => 'Catering', 'icon' => 'utensils'],
                    ['name' => 'AV & Production', 'icon' => 'video'], ['name' => 'Registration/Badging', 'icon' => 'user'],
                ],
                'default_task_categories' => [
                    ['name' => 'Pre-Event', 'color' => '#3B82F6'], ['name' => 'On The Day', 'color' => '#10B981'],
                    ['name' => 'Speakers', 'color' => '#7C3AED'], ['name' => 'Post-Event', 'color' => '#78716C'],
                ],
                'default_roles' => ['Conference Manager', 'Operations', 'Finance'],
                'recommended_feature_flags' => ['rsvp', 'runsheet', 'booking_forms'],
            ],
            [
                'key'  => 'entertainment',
                'name' => 'Entertainment',
                'icon' => '🎭',
                'description' => 'For live entertainment and stage show producers.',
                'terminology' => ['guest' => 'Audience', 'vendor' => 'Supplier', 'guest_plural' => 'Audience', 'vendor_plural' => 'Suppliers'],
                'default_event_types' => [
                    ['name' => 'Live Show',    'icon' => 'star',  'color' => '#7C3AED'],
                    ['name' => 'Concert',      'icon' => 'music', 'color' => '#EF4444'],
                    ['name' => 'Stage Play',   'icon' => 'video', 'color' => '#3B82F6'],
                ],
                'default_vendor_categories' => [
                    ['name' => 'Equipment Rental', 'icon' => 'camera'], ['name' => 'Venue', 'icon' => 'building'],
                    ['name' => 'Security', 'icon' => 'shield'], ['name' => 'Ushers', 'icon' => 'users'],
                ],
                'default_task_categories' => [
                    ['name' => 'Pre-Production', 'color' => '#3B82F6'], ['name' => 'Show Day', 'color' => '#10B981'],
                    ['name' => 'Post-Show', 'color' => '#78716C'],
                ],
                'default_roles' => ['Show Producer', 'Stage Manager', 'Operations'],
                'recommended_feature_flags' => ['runsheet', 'vendor_portal'],
            ],
            [
                'key'  => 'exhibition',
                'name' => 'Exhibition',
                'icon' => '🖼️',
                'description' => 'For trade shows and exhibition organizers.',
                'terminology' => ['guest' => 'Visitor', 'vendor' => 'Exhibitor', 'guest_plural' => 'Visitors', 'vendor_plural' => 'Exhibitors'],
                'default_event_types' => [
                    ['name' => 'Trade Show',  'icon' => 'building', 'color' => '#3B82F6'],
                    ['name' => 'Exhibition',  'icon' => 'star',     'color' => '#7C3AED'],
                ],
                'default_vendor_categories' => [
                    ['name' => 'Booth/Stand Builders', 'icon' => 'building'], ['name' => 'AV & Production', 'icon' => 'video'],
                    ['name' => 'Catering', 'icon' => 'utensils'], ['name' => 'Security', 'icon' => 'shield'],
                ],
                'default_task_categories' => [
                    ['name' => 'Pre-Event', 'color' => '#3B82F6'], ['name' => 'On The Day', 'color' => '#10B981'],
                    ['name' => 'Post-Event', 'color' => '#78716C'],
                ],
                'default_roles' => ['Exhibition Manager', 'Operations'],
                'recommended_feature_flags' => ['runsheet', 'booking_forms'],
            ],
            [
                'key'  => 'full_service',
                'name' => 'Full-Service / Multiple Event Types',
                'icon' => '✨',
                'description' => 'For companies handling a wide mix of event types under one roof.',
                'terminology' => [],
                'default_event_types' => [
                    ['name' => 'Wedding', 'icon' => 'rings', 'color' => '#7C3AED'],
                    ['name' => 'Corporate Event', 'icon' => 'briefcase', 'color' => '#3B82F6'],
                    ['name' => 'Birthday', 'icon' => 'cake', 'color' => '#F59E0B'],
                    ['name' => 'Concert', 'icon' => 'music', 'color' => '#EF4444'],
                    ['name' => 'Conference', 'icon' => 'mic', 'color' => '#6366F1'],
                ],
                'default_vendor_categories' => [
                    ['name' => 'Venue', 'icon' => 'building'], ['name' => 'Catering', 'icon' => 'utensils'],
                    ['name' => 'Photography', 'icon' => 'camera'], ['name' => 'AV & Production', 'icon' => 'video'],
                    ['name' => 'Decoration', 'icon' => 'flower'], ['name' => 'Security', 'icon' => 'shield'],
                ],
                'default_task_categories' => [
                    ['name' => 'Pre-Event', 'color' => '#3B82F6'], ['name' => 'Logistics', 'color' => '#F59E0B'],
                    ['name' => 'On The Day', 'color' => '#10B981'], ['name' => 'Post-Event', 'color' => '#78716C'],
                ],
                'default_roles' => ['Coordinator', 'Finance', 'Operations'],
                'recommended_feature_flags' => ['rsvp', 'runsheet', 'client_portal', 'booking_forms', 'vendor_portal'],
            ],
            [
                'key'  => 'other',
                'name' => 'Other',
                'icon' => '✨',
                'description' => 'General purpose — customize everything yourself.',
                'terminology' => [],
                'default_event_types' => [
                    ['name' => 'Event', 'icon' => 'star', 'color' => '#7C3AED'],
                ],
                'default_vendor_categories' => [
                    ['name' => 'Venue', 'icon' => 'building'], ['name' => 'Catering', 'icon' => 'utensils'], ['name' => 'Other', 'icon' => 'more'],
                ],
                'default_task_categories' => [
                    ['name' => 'Pre-Event', 'color' => '#3B82F6'], ['name' => 'On The Day', 'color' => '#10B981'], ['name' => 'Post-Event', 'color' => '#78716C'],
                ],
                'default_roles' => ['Coordinator', 'Operations'],
                'recommended_feature_flags' => ['runsheet'],
            ],
        ];

        foreach ($profiles as $i => $profile) {
            IndustryProfile::updateOrCreate(
                ['key' => $profile['key']],
                array_merge($profile, ['sort_order' => $i, 'is_active' => true])
            );
        }
    }
}
