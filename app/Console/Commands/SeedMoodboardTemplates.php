<?php

namespace App\Console\Commands;

use App\Models\Central\IndustryProfile;
use App\Models\Central\Tenant;
use App\Models\Tenant\Moodboard;
use App\Models\Tenant\MoodboardItem;
use Illuminate\Console\Command;

class SeedMoodboardTemplates extends Command
{
    protected $signature = 'koordli:seed-moodboard-templates';
    protected $description = 'Seeds a starter moodboard template (colors, notes, empty image slots) into every Industry Profile, for every tenant.';

    public function handle(): void
    {
        $data = $this->templateData();
        $profilesByKey = IndustryProfile::all()->keyBy('key');

        Tenant::each(function (Tenant $tenant) use ($data, $profilesByKey) {
            foreach ($data as $profileKey => $template) {
                $profile = $profilesByKey->get($profileKey);

                if (!$profile) {
                    $this->warn("Industry profile key '{$profileKey}' not found — check IndustryProfile.key values.");
                    continue;
                }

                $alreadyExists = Moodboard::where('tenant_id', $tenant->id)
                    ->where('is_template', true)
                    ->where('industry_profile_id', $profile->id)
                    ->where('title', $template['title'])
                    ->exists();

                if ($alreadyExists) continue;

                $board = Moodboard::create([
                    'tenant_id'           => $tenant->id,
                    'event_id'            => null,
                    'title'               => $template['title'],
                    'description'         => $template['description'],
                    'status'              => 'draft',
                    'is_template'         => true,
                    'industry_profile_id' => $profile->id,
                ]);

                $order = 0;
                foreach ($template['items'] as $item) {
                    MoodboardItem::create([
                        'tenant_id'    => $tenant->id,
                        'moodboard_id' => $board->id,
                        'type'         => $item['type'],
                        'pos_x'        => $item['x'],
                        'pos_y'        => $item['y'],
                        'width'        => $item['w'],
                        'height'       => $item['h'],
                        'sort_order'   => $order++,
                        'data'         => $item['data'] ?? null,
                    ]);
                }

                $this->info("Tenant #{$tenant->id}: seeded '{$template['title']}'.");
            }
        });

        $this->info('Done.');
    }

    /**
     * Every template follows the same honest formula: a few color
     * swatches (a real, considered palette for that occasion), 1-2 note
     * items suggesting mood/direction, and several genuinely EMPTY image
     * slots — never fabricated photography, since none exists. This
     * gives a planner a real starting composition to drop their own
     * photos into, not a blank canvas.
     */
    private function templateData(): array
    {
        return [
            'wedding_events' => [
                'title' => 'Birthday / Milestone Celebration Moodboard',
                'description' => 'Starter layout for a birthday or milestone celebration — swap in your own photos.',
                'items' => [
                    ['type' => 'color', 'x' => 20,  'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#F59E0B', 'name' => 'Golden Amber']],
                    ['type' => 'color', 'x' => 190, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#EC4899', 'name' => 'Celebration Pink']],
                    ['type' => 'color', 'x' => 360, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#FEF3C7', 'name' => 'Soft Cream']],
                    ['type' => 'note',  'x' => 20,  'y' => 180, 'w' => 300, 'h' => 140, 'data' => ['content' => "Mood: playful, warm, celebratory. Think balloons, gold accents, confetti textures."]],
                    ['type' => 'empty', 'x' => 340, 'y' => 180, 'w' => 220, 'h' => 260],
                    ['type' => 'empty', 'x' => 580, 'y' => 20,  'w' => 220, 'h' => 260],
                    ['type' => 'empty', 'x' => 20,  'y' => 340, 'w' => 220, 'h' => 220],
                    ['type' => 'empty', 'x' => 260, 'y' => 340, 'w' => 220, 'h' => 220],
                ],
            ],

            'corporate' => [
                'title' => 'Corporate Event Moodboard',
                'description' => 'Starter layout for a professional corporate event — swap in your own photos.',
                'items' => [
                    ['type' => 'color', 'x' => 20,  'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#1E3A8A', 'name' => 'Corporate Navy']],
                    ['type' => 'color', 'x' => 190, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#94A3B8', 'name' => 'Slate Grey']],
                    ['type' => 'color', 'x' => 360, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#F8FAFC', 'name' => 'Clean White']],
                    ['type' => 'note',  'x' => 20,  'y' => 180, 'w' => 300, 'h' => 140, 'data' => ['content' => "Mood: polished, minimal, brand-forward. Clean lines, structured staging, professional lighting."]],
                    ['type' => 'empty', 'x' => 340, 'y' => 180, 'w' => 220, 'h' => 260],
                    ['type' => 'empty', 'x' => 580, 'y' => 20,  'w' => 220, 'h' => 260],
                    ['type' => 'empty', 'x' => 20,  'y' => 340, 'w' => 220, 'h' => 220],
                    ['type' => 'empty', 'x' => 260, 'y' => 340, 'w' => 220, 'h' => 220],
                ],
            ],

            'production' => [
                'title' => 'Production Moodboard',
                'description' => 'Starter layout for a stage, film, or media production — swap in your own photos.',
                'items' => [
                    ['type' => 'color', 'x' => 20,  'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#18181B', 'name' => 'Stage Black']],
                    ['type' => 'color', 'x' => 190, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#EF4444', 'name' => 'Spotlight Red']],
                    ['type' => 'color', 'x' => 360, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#FDE047', 'name' => 'Stage Gold']],
                    ['type' => 'note',  'x' => 20,  'y' => 180, 'w' => 300, 'h' => 140, 'data' => ['content' => "Mood: dramatic, high-contrast, bold lighting. Reference set design, lighting rigs, and staging angles."]],
                    ['type' => 'empty', 'x' => 340, 'y' => 180, 'w' => 220, 'h' => 260],
                    ['type' => 'empty', 'x' => 580, 'y' => 20,  'w' => 220, 'h' => 260],
                    ['type' => 'empty', 'x' => 20,  'y' => 340, 'w' => 220, 'h' => 220],
                    ['type' => 'empty', 'x' => 260, 'y' => 340, 'w' => 220, 'h' => 220],
                ],
            ],

            'church' => [
                'title' => 'Church Event Moodboard',
                'description' => 'Starter layout for a church service or congregation event — swap in your own photos.',
                'items' => [
                    ['type' => 'color', 'x' => 20,  'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#FFFFFF', 'name' => 'Pure White']],
                    ['type' => 'color', 'x' => 190, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#B45309', 'name' => 'Warm Gold']],
                    ['type' => 'color', 'x' => 360, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#7C2D12', 'name' => 'Deep Maroon']],
                    ['type' => 'note',  'x' => 20,  'y' => 180, 'w' => 300, 'h' => 140, 'data' => ['content' => "Mood: reverent, warm, welcoming. Soft natural light, floral accents, dignified staging."]],
                    ['type' => 'empty', 'x' => 340, 'y' => 180, 'w' => 220, 'h' => 260],
                    ['type' => 'empty', 'x' => 580, 'y' => 20,  'w' => 220, 'h' => 260],
                    ['type' => 'empty', 'x' => 20,  'y' => 340, 'w' => 220, 'h' => 220],
                    ['type' => 'empty', 'x' => 260, 'y' => 340, 'w' => 220, 'h' => 220],
                ],
            ],

            'conference' => [
                'title' => 'Conference Moodboard',
                'description' => 'Starter layout for a multi-session conference or summit — swap in your own photos.',
                'items' => [
                    ['type' => 'color', 'x' => 20,  'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#0F172A', 'name' => 'Deep Navy']],
                    ['type' => 'color', 'x' => 190, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#0EA5E9', 'name' => 'Tech Blue']],
                    ['type' => 'color', 'x' => 360, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#F1F5F9', 'name' => 'Light Grey']],
                    ['type' => 'note',  'x' => 20,  'y' => 180, 'w' => 300, 'h' => 140, 'data' => ['content' => "Mood: modern, tech-forward, structured. Clean stage backdrops, breakout signage, panel setups."]],
                    ['type' => 'empty', 'x' => 340, 'y' => 180, 'w' => 220, 'h' => 260],
                    ['type' => 'empty', 'x' => 580, 'y' => 20,  'w' => 220, 'h' => 260],
                    ['type' => 'empty', 'x' => 20,  'y' => 340, 'w' => 220, 'h' => 220],
                    ['type' => 'empty', 'x' => 260, 'y' => 340, 'w' => 220, 'h' => 220],
                ],
            ],

            'entertainment' => [
                'title' => 'Entertainment Event Moodboard',
                'description' => 'Starter layout for a concert, show, or live entertainment event — swap in your own photos.',
                'items' => [
                    ['type' => 'color', 'x' => 20,  'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#4C1D95', 'name' => 'Stage Violet']],
                    ['type' => 'color', 'x' => 190, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#F97316', 'name' => 'Neon Orange']],
                    ['type' => 'color', 'x' => 360, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#000000', 'name' => 'Concert Black']],
                    ['type' => 'note',  'x' => 20,  'y' => 180, 'w' => 300, 'h' => 140, 'data' => ['content' => "Mood: energetic, bold, high-contrast. Concert lighting, crowd energy, dynamic stage angles."]],
                    ['type' => 'empty', 'x' => 340, 'y' => 180, 'w' => 220, 'h' => 260],
                    ['type' => 'empty', 'x' => 580, 'y' => 20,  'w' => 220, 'h' => 260],
                    ['type' => 'empty', 'x' => 20,  'y' => 340, 'w' => 220, 'h' => 220],
                    ['type' => 'empty', 'x' => 260, 'y' => 340, 'w' => 220, 'h' => 220],
                ],
            ],

            'exhibition' => [
                'title' => 'Exhibition Moodboard',
                'description' => 'Starter layout for a trade show, exhibition, or expo — swap in your own photos.',
                'items' => [
                    ['type' => 'color', 'x' => 20,  'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#FFFFFF', 'name' => 'Booth White']],
                    ['type' => 'color', 'x' => 190, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#059669', 'name' => 'Signal Green']],
                    ['type' => 'color', 'x' => 360, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#1F2937', 'name' => 'Display Grey']],
                    ['type' => 'note',  'x' => 20,  'y' => 180, 'w' => 300, 'h' => 140, 'data' => ['content' => "Mood: open, wayfinding-friendly, brand-visible. Clean booth layouts, clear signage, floor traffic flow."]],
                    ['type' => 'empty', 'x' => 340, 'y' => 180, 'w' => 220, 'h' => 260],
                    ['type' => 'empty', 'x' => 580, 'y' => 20,  'w' => 220, 'h' => 260],
                    ['type' => 'empty', 'x' => 20,  'y' => 340, 'w' => 220, 'h' => 220],
                    ['type' => 'empty', 'x' => 260, 'y' => 340, 'w' => 220, 'h' => 220],
                ],
            ],

            'other' => [
                'title' => 'General Event Moodboard',
                'description' => 'A flexible starter layout suitable for any event type — swap in your own photos.',
                'items' => [
                    ['type' => 'color', 'x' => 20,  'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#7C3AED', 'name' => 'Signature Violet']],
                    ['type' => 'color', 'x' => 190, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#F5F5F4', 'name' => 'Neutral Grey']],
                    ['type' => 'note',  'x' => 20,  'y' => 180, 'w' => 300, 'h' => 140, 'data' => ['content' => "Set your own mood and direction here — this board is a flexible starting point."]],
                    ['type' => 'empty', 'x' => 340, 'y' => 180, 'w' => 220, 'h' => 260],
                    ['type' => 'empty', 'x' => 580, 'y' => 20,  'w' => 220, 'h' => 260],
                    ['type' => 'empty', 'x' => 20,  'y' => 340, 'w' => 220, 'h' => 220],
                    ['type' => 'empty', 'x' => 260, 'y' => 340, 'w' => 220, 'h' => 220],
                ],
            ],
        ];
    }
}