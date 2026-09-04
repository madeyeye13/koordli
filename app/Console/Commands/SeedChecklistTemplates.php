<?php

namespace App\Console\Commands;

use App\Models\Central\IndustryProfile;
use App\Models\Central\Tenant;
use App\Models\Tenant\ChecklistTemplate;
use App\Models\Tenant\ChecklistTemplateItem;
use Illuminate\Console\Command;

class SeedChecklistTemplates extends Command
{
    protected $signature = 'koordli:seed-checklist-templates';
    protected $description = 'Seeds 8 industry-standard checklist templates (one per Industry Profile) into every tenant, skipping ones that already have them.';

    public function handle(): void
    {
        $data = $this->templateData();
        $profilesByKey = IndustryProfile::all()->keyBy('key');

        Tenant::each(function (Tenant $tenant) use ($data, $profilesByKey) {
            // $data is now: [profileKey => [ [template], [template], ... ]]
            // — one profile can have MULTIPLE templates, so the outer key
            // is purely the real IndustryProfile.key, and each profile's
            // value is a LIST of templates, not a single template.
            foreach ($data as $profileKey => $templatesForProfile) {
                $profile = $profilesByKey->get($profileKey);

                if (!$profile) {
                    $this->warn("Industry profile key '{$profileKey}' not found — check IndustryProfile.key values.");
                    continue;
                }

                foreach ($templatesForProfile as $template) {
                    $alreadyExists = ChecklistTemplate::where('tenant_id', $tenant->id)
                        ->where('industry_profile_id', $profile->id)
                        ->where('title', $template['title'])
                        ->exists();

                    if ($alreadyExists) continue;

                    $checklistTemplate = ChecklistTemplate::create([
                        'tenant_id'           => $tenant->id,
                        'title'               => $template['title'],
                        'description'         => $template['description'],
                        'industry_profile_id' => $profile->id,
                    ]);

                    $order = 0;
                    foreach ($template['phases'] as $phase => $items) {
                        foreach ($items as $item) {
                            ChecklistTemplateItem::create([
                                'tenant_id'             => $tenant->id,
                                'checklist_template_id' => $checklistTemplate->id,
                                'title'                 => $item['title'],
                                'description'           => $item['description'] ?? null,
                                'phase'                 => $phase,
                                'sort_order'            => $order++,
                            ]);
                        }
                    }

                    $this->info("Tenant #{$tenant->id}: seeded '{$template['title']}'.");
                }
            }
        });

        $this->info('Done.');
    }

        private function templateData(): array
    {
        return [
            'wedding_events' => [
                [
                    'title' => 'Full Wedding Planning Checklist',
                    'description' => 'A comprehensive milestone checklist covering the full wedding planning journey.',
                    'phases' => [
                        '12_plus_months' => [
                            ['title' => 'Set overall budget', 'description' => 'Agree on a total spend and how costs will be split.'],
                            ['title' => 'Choose a wedding date and backup date'],
                            ['title' => 'Book ceremony and reception venue'],
                            ['title' => 'Draft the initial guest list'],
                            ['title' => 'Hire a planner or coordinator (if using one)'],
                        ],
                        '6_9_months' => [
                            ['title' => 'Book photographer and videographer'],
                            ['title' => 'Book catering and confirm menu direction'],
                            ['title' => 'Choose wedding party and send invites to be part of it'],
                            ['title' => 'Book entertainment (band/DJ)'],
                            ['title' => 'Begin dress/attire shopping'],
                        ],
                        '3_6_months' => [
                            ['title' => 'Send save-the-dates'],
                            ['title' => 'Book officiant'],
                            ['title' => 'Order invitations'],
                            ['title' => 'Plan honeymoon'],
                            ['title' => 'Finalize florist and decor direction'],
                        ],
                        '1_3_months' => [
                            ['title' => 'Send formal invitations'],
                            ['title' => 'Finalize seating chart draft'],
                            ['title' => 'Confirm final headcount with caterer'],
                            ['title' => 'Attire fittings'],
                            ['title' => 'Apply for marriage license'],
                        ],
                        '2_4_weeks' => [
                            ['title' => 'Confirm all vendor arrival times'],
                            ['title' => 'Finalize timeline/runsheet with vendors'],
                            ['title' => 'Final dress fitting'],
                            ['title' => 'Prepare final payments for vendors'],
                        ],
                        '1_week' => [
                            ['title' => 'Confirm final guest count'],
                            ['title' => 'Pack for honeymoon'],
                            ['title' => 'Delegate day-of tasks to wedding party'],
                            ['title' => 'Rehearsal dinner'],
                        ],
                        'day_of' => [
                            ['title' => 'Vendor check-in and setup confirmation'],
                            ['title' => 'Distribute final payments/tips'],
                            ['title' => 'Enjoy the day'],
                        ],
                    ],
                ],
                [
                    'title' => 'Birthday / Milestone Celebration Checklist',
                    'description' => 'Milestones for planning a birthday or milestone age celebration.',
                    'phases' => [
                        '12_plus_months' => [
                            ['title' => 'Set budget and guest count target'],
                            ['title' => 'Choose date and lock venue'],
                        ],
                        '6_9_months' => [
                            ['title' => 'Choose party theme/direction'],
                            ['title' => 'Book photographer (if desired)'],
                        ],
                        '3_6_months' => [
                            ['title' => 'Book catering or caterer'],
                            ['title' => 'Book entertainment (DJ, MC, performers)'],
                            ['title' => 'Order/design invitations'],
                        ],
                        '1_3_months' => [
                            ['title' => 'Send invitations'],
                            ['title' => 'Order cake'],
                            ['title' => 'Confirm decor and rentals'],
                        ],
                        '2_4_weeks' => [
                            ['title' => 'Confirm final headcount'],
                            ['title' => 'Confirm all vendor timing'],
                        ],
                        '1_week' => [
                            ['title' => 'Pick up/confirm cake delivery'],
                            ['title' => 'Prepare party favors'],
                        ],
                        'day_of' => [
                            ['title' => 'Venue setup and decor'],
                            ['title' => 'Vendor check-in'],
                        ],
                    ],
                ],
                [
                    'title' => 'House Warming Checklist',
                    'description' => 'Milestones for planning a house warming or new-home celebration.',
                    'phases' => [
                        '3_6_months' => [
                            ['title' => 'Set budget and guest list'],
                            ['title' => 'Choose date'],
                        ],
                        '1_3_months' => [
                            ['title' => 'Send invitations'],
                            ['title' => 'Plan catering/menu (self-catered or vendor)'],
                            ['title' => 'Plan decor direction for the space'],
                        ],
                        '2_4_weeks' => [
                            ['title' => 'Confirm final guest count'],
                            ['title' => 'Order any rentals (seating, tables)'],
                        ],
                        '1_week' => [
                            ['title' => 'Final home preparation/cleaning'],
                            ['title' => 'Confirm all deliveries'],
                        ],
                        'day_of' => [
                            ['title' => 'Setup and decor'],
                            ['title' => 'Welcome guests / house tour plan'],
                        ],
                    ],
                ],
                [
                    'title' => 'Memorial / Burial Service Checklist',
                    'description' => 'Milestones for planning a memorial, funeral, or burial service with care and sensitivity.',
                    'phases' => [
                        '1_3_months' => [
                            ['title' => 'Confirm service date with family and officiant'],
                            ['title' => 'Book venue/church and burial site'],
                            ['title' => 'Set budget with family'],
                        ],
                        '2_4_weeks' => [
                            ['title' => 'Prepare obituary and announcement'],
                            ['title' => 'Confirm officiant and order of service'],
                            ['title' => 'Arrange catering for repast/reception'],
                        ],
                        '1_week' => [
                            ['title' => 'Print programs/order of service'],
                            ['title' => 'Confirm transportation and family logistics'],
                            ['title' => 'Confirm florist and tribute arrangements'],
                        ],
                        'day_of' => [
                            ['title' => 'Venue setup and seating arrangement'],
                            ['title' => 'Family and guest coordination'],
                            ['title' => 'Repast/reception setup'],
                        ],
                    ],
                ],
            ],

            'corporate' => [[
                'title' => 'Corporate Event Checklist',
                'description' => 'Milestones for planning a professional corporate event or company function.',
                'phases' => [
                    '12_plus_months' => [
                        ['title' => 'Define event objective and target audience'],
                        ['title' => 'Set budget and get internal approval'],
                        ['title' => 'Select and book venue'],
                    ],
                    '6_9_months' => [
                        ['title' => 'Confirm keynote speakers or presenters'],
                        ['title' => 'Book AV and production vendor'],
                        ['title' => 'Design event branding and materials'],
                    ],
                    '3_6_months' => [
                        ['title' => 'Open registration/RSVP'],
                        ['title' => 'Confirm catering'],
                        ['title' => 'Arrange transportation/parking logistics'],
                    ],
                    '1_3_months' => [
                        ['title' => 'Finalize agenda and run-of-show'],
                        ['title' => 'Confirm sponsor deliverables (if any)'],
                        ['title' => 'Send attendee reminders'],
                    ],
                    '2_4_weeks' => [
                        ['title' => 'Confirm final attendee count'],
                        ['title' => 'Print name badges/signage'],
                        ['title' => 'Brief internal staff on their roles'],
                    ],
                    '1_week' => [
                        ['title' => 'Final walkthrough with venue and AV team'],
                        ['title' => 'Confirm all vendor arrival windows'],
                    ],
                    'day_of' => [
                        ['title' => 'Registration desk setup'],
                        ['title' => 'AV/tech check before doors open'],
                        ['title' => 'Post-event feedback collection'],
                    ],
                ],
            ]],

            'production' => [[
                'title' => 'Production Event Checklist',
                'description' => 'Milestones for planning a stage, film, or media production event.',
                'phases' => [
                    '12_plus_months' => [
                        ['title' => 'Finalize concept, script, or creative direction'],
                        ['title' => 'Secure venue/location and confirm technical capacity'],
                        ['title' => 'Set production budget'],
                    ],
                    '6_9_months' => [
                        ['title' => 'Book lighting and sound design team'],
                        ['title' => 'Finalize set design direction'],
                        ['title' => 'Cast or book talent'],
                    ],
                    '3_6_months' => [
                        ['title' => 'Confirm rehearsal schedule'],
                        ['title' => 'Order/build set pieces and props'],
                        ['title' => 'Finalize technical rider requirements'],
                    ],
                    '1_3_months' => [
                        ['title' => 'Tech rehearsal scheduled'],
                        ['title' => 'Confirm crew call sheet'],
                        ['title' => 'Marketing/promotion push begins'],
                    ],
                    '2_4_weeks' => [
                        ['title' => 'Full dress rehearsal'],
                        ['title' => 'Confirm load-in/load-out schedule'],
                        ['title' => 'Finalize safety plan'],
                    ],
                    '1_week' => [
                        ['title' => 'Final tech check'],
                        ['title' => 'Confirm all crew and talent call times'],
                    ],
                    'day_of' => [
                        ['title' => 'Load-in and set build'],
                        ['title' => 'Sound and lighting check'],
                        ['title' => 'Strike/load-out plan confirmed'],
                    ],
                ],
            ]],

            'church' => [[
                'title' => 'Church Event Checklist',
                'description' => 'Milestones for planning a church service, ceremony, or congregation event.',
                'phases' => [
                    '12_plus_months' => [
                        ['title' => 'Confirm date with church leadership/calendar'],
                        ['title' => 'Set budget (if applicable)'],
                    ],
                    '6_9_months' => [
                        ['title' => 'Confirm officiating minister/leader'],
                        ['title' => 'Book musicians/choir'],
                    ],
                    '3_6_months' => [
                        ['title' => 'Plan order of service'],
                        ['title' => 'Confirm decor and seating arrangement'],
                    ],
                    '1_3_months' => [
                        ['title' => 'Print/prepare programs or bulletins'],
                        ['title' => 'Confirm volunteer roles (ushers, greeters)'],
                    ],
                    '2_4_weeks' => [
                        ['title' => 'Final run-through with participants'],
                        ['title' => 'Confirm sound system needs'],
                    ],
                    '1_week' => [
                        ['title' => 'Confirm final headcount for seating'],
                        ['title' => 'Brief all volunteers on their roles'],
                    ],
                    'day_of' => [
                        ['title' => 'Venue setup and sound check'],
                        ['title' => 'Volunteer check-in'],
                    ],
                ],
            ]],

            'conference' => [[
                'title' => 'Conference Checklist',
                'description' => 'Milestones for planning a multi-session conference or summit.',
                'phases' => [
                    '12_plus_months' => [
                        ['title' => 'Define conference theme and tracks'],
                        ['title' => 'Secure venue with breakout room capacity'],
                        ['title' => 'Set budget and sponsorship targets'],
                    ],
                    '6_9_months' => [
                        ['title' => 'Confirm keynote and session speakers'],
                        ['title' => 'Open call for papers/proposals (if applicable)'],
                        ['title' => 'Secure sponsors'],
                    ],
                    '3_6_months' => [
                        ['title' => 'Open attendee registration'],
                        ['title' => 'Finalize session schedule/agenda'],
                        ['title' => 'Book AV for all breakout rooms'],
                    ],
                    '1_3_months' => [
                        ['title' => 'Confirm speaker travel/logistics'],
                        ['title' => 'Finalize catering for all days'],
                        ['title' => 'Prepare attendee welcome materials'],
                    ],
                    '2_4_weeks' => [
                        ['title' => 'Confirm final attendee numbers per session'],
                        ['title' => 'Print badges and signage'],
                    ],
                    '1_week' => [
                        ['title' => 'Final venue walkthrough'],
                        ['title' => 'Brief all volunteers/staff'],
                    ],
                    'day_of' => [
                        ['title' => 'Registration and check-in setup'],
                        ['title' => 'Session room readiness checks'],
                        ['title' => 'Post-conference survey sent'],
                    ],
                ],
            ]],

            'entertainment' => [[
                'title' => 'Entertainment Event Checklist',
                'description' => 'Milestones for planning a concert, show, or live entertainment event.',
                'phases' => [
                    '12_plus_months' => [
                        ['title' => 'Book headline act/performer'],
                        ['title' => 'Secure venue and confirm capacity'],
                        ['title' => 'Set ticketing and budget targets'],
                    ],
                    '6_9_months' => [
                        ['title' => 'Book supporting acts'],
                        ['title' => 'Confirm technical rider with performers'],
                        ['title' => 'Begin marketing campaign'],
                    ],
                    '3_6_months' => [
                        ['title' => 'Open ticket sales'],
                        ['title' => 'Book security and crowd management'],
                        ['title' => 'Confirm stage/production vendor'],
                    ],
                    '1_3_months' => [
                        ['title' => 'Finalize show run-of-show/setlist timing'],
                        ['title' => 'Confirm performer travel and hospitality'],
                    ],
                    '2_4_weeks' => [
                        ['title' => 'Confirm ticket sales vs. capacity'],
                        ['title' => 'Finalize load-in schedule'],
                    ],
                    '1_week' => [
                        ['title' => 'Final production/technical check'],
                        ['title' => 'Brief security and staff'],
                    ],
                    'day_of' => [
                        ['title' => 'Load-in and soundcheck'],
                        ['title' => 'Doors open / gate management'],
                        ['title' => 'Load-out and venue clear'],
                    ],
                ],
            ]],

            'exhibition' => [[
                'title' => 'Exhibition Checklist',
                'description' => 'Milestones for planning a trade show, exhibition, or expo event.',
                'phases' => [
                    '12_plus_months' => [
                        ['title' => 'Secure exhibition hall/venue'],
                        ['title' => 'Define exhibitor categories and floor plan concept'],
                        ['title' => 'Set budget and exhibitor pricing'],
                    ],
                    '6_9_months' => [
                        ['title' => 'Open exhibitor booth sales/applications'],
                        ['title' => 'Confirm floor plan layout'],
                        ['title' => 'Book booth/stand contractor'],
                    ],
                    '3_6_months' => [
                        ['title' => 'Open visitor registration'],
                        ['title' => 'Confirm signage and wayfinding plan'],
                        ['title' => 'Book security and logistics vendor'],
                    ],
                    '1_3_months' => [
                        ['title' => 'Send exhibitor pack (setup times, rules)'],
                        ['title' => 'Confirm final floor plan with all exhibitors'],
                    ],
                    '2_4_weeks' => [
                        ['title' => 'Confirm exhibitor booth assignments finalized'],
                        ['title' => 'Print visitor badges and signage'],
                    ],
                    '1_week' => [
                        ['title' => 'Final venue walkthrough'],
                        ['title' => 'Brief floor staff and volunteers'],
                    ],
                    'day_of' => [
                        ['title' => 'Exhibitor move-in supervision'],
                        ['title' => 'Visitor registration desk setup'],
                        ['title' => 'Move-out/breakdown supervision'],
                    ],
                ],
            ]],

            'other' => [[
                'title' => 'General Event Checklist',
                'description' => 'A flexible, general-purpose checklist suitable for any event type not covered by a specific profile.',
                'phases' => [
                    '12_plus_months' => [
                        ['title' => 'Define event goal and scope'],
                        ['title' => 'Set budget'],
                        ['title' => 'Book venue'],
                    ],
                    '6_9_months' => [
                        ['title' => 'Book key vendors'],
                        ['title' => 'Confirm guest list approach'],
                    ],
                    '3_6_months' => [
                        ['title' => 'Send invitations/announcements'],
                        ['title' => 'Confirm catering (if applicable)'],
                    ],
                    '1_3_months' => [
                        ['title' => 'Finalize run-of-show'],
                        ['title' => 'Confirm final vendor details'],
                    ],
                    '2_4_weeks' => [
                        ['title' => 'Confirm final headcount'],
                        ['title' => 'Final vendor payments prepared'],
                    ],
                    '1_week' => [
                        ['title' => 'Final walkthrough/check-in with vendors'],
                    ],
                    'day_of' => [
                        ['title' => 'Setup and vendor check-in'],
                        ['title' => 'Post-event wrap-up'],
                    ],
                ],
            ]],
        ];
    }
}