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
    protected $description = 'Seeds starter moodboard templates (colors, notes, empty image slots) into every Industry Profile, for every tenant.';

    public function handle(): void
    {
        $data = $this->templateData();
        $profilesByKey = IndustryProfile::all()->keyBy('key');

        Tenant::each(function (Tenant $tenant) use ($data, $profilesByKey) {
            foreach ($data as $profileKey => $templates) {
                $profile = $profilesByKey->get($profileKey);

                if (!$profile) {
                    $this->warn("Industry profile key '{$profileKey}' not found — check IndustryProfile.key values.");
                    continue;
                }

                foreach ($templates as $template) {
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
     *
     * Each profile now returns an ARRAY of templates (not a single one),
     * giving planners real variety to choose from within their industry.
     */
    private function templateData(): array
    {
        return [
            'wedding_events' => [
                [
                    'title' => 'Wedding Moodboard',
                    'description' => 'Starter layout for a wedding celebration — swap in your own photos.',
                    'items' => [
                        ['type' => 'color', 'x' => 20,  'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#FDF2F8', 'name' => 'Blush Petal']],
                        ['type' => 'color', 'x' => 190, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#D4AF37', 'name' => 'Antique Gold']],
                        ['type' => 'color', 'x' => 360, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#FFFFFF', 'name' => 'Ivory']],
                        ['type' => 'note',  'x' => 20,  'y' => 180, 'w' => 300, 'h' => 140, 'data' => ['content' => "Mood: romantic, timeless, elegant. Soft florals, candlelight, flowing fabrics."]],
                        ['type' => 'empty', 'x' => 340, 'y' => 180, 'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 580, 'y' => 20,  'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 20,  'y' => 340, 'w' => 220, 'h' => 220],
                        ['type' => 'empty', 'x' => 260, 'y' => 340, 'w' => 220, 'h' => 220],
                    ],
                ],
                [
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
                [
                    'title' => 'Anniversary Moodboard',
                    'description' => 'Starter layout for an anniversary celebration — swap in your own photos.',
                    'items' => [
                        ['type' => 'color', 'x' => 20,  'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#7C2D12', 'name' => 'Rich Burgundy']],
                        ['type' => 'color', 'x' => 190, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#D4AF37', 'name' => 'Champagne Gold']],
                        ['type' => 'color', 'x' => 360, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#1C1917', 'name' => 'Deep Charcoal']],
                        ['type' => 'note',  'x' => 20,  'y' => 180, 'w' => 300, 'h' => 140, 'data' => ['content' => "Mood: intimate, reflective, refined. Soft lighting, shared history, understated luxury."]],
                        ['type' => 'empty', 'x' => 340, 'y' => 180, 'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 580, 'y' => 20,  'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 20,  'y' => 340, 'w' => 220, 'h' => 220],
                        ['type' => 'empty', 'x' => 260, 'y' => 340, 'w' => 220, 'h' => 220],
                    ],
                ],
            ],

            'corporate' => [
                [
                    'title' => 'Product Launch Moodboard',
                    'description' => 'Starter layout for a product launch or brand unveiling — swap in your own photos.',
                    'items' => [
                        ['type' => 'color', 'x' => 20,  'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#000000', 'name' => 'Launch Black']],
                        ['type' => 'color', 'x' => 190, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#6366F1', 'name' => 'Brand Indigo']],
                        ['type' => 'color', 'x' => 360, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#FFFFFF', 'name' => 'Spotlight White']],
                        ['type' => 'note',  'x' => 20,  'y' => 180, 'w' => 300, 'h' => 140, 'data' => ['content' => "Mood: sleek, dramatic reveal, high production value. Focused lighting, minimal staging."]],
                        ['type' => 'empty', 'x' => 340, 'y' => 180, 'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 580, 'y' => 20,  'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 20,  'y' => 340, 'w' => 220, 'h' => 220],
                        ['type' => 'empty', 'x' => 260, 'y' => 340, 'w' => 220, 'h' => 220],
                    ],
                ],
                [
                    'title' => 'Annual Gala Moodboard',
                    'description' => 'Starter layout for a corporate gala or awards dinner — swap in your own photos.',
                    'items' => [
                        ['type' => 'color', 'x' => 20,  'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#1E3A8A', 'name' => 'Corporate Navy']],
                        ['type' => 'color', 'x' => 190, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#D4AF37', 'name' => 'Formal Gold']],
                        ['type' => 'color', 'x' => 360, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#F8FAFC', 'name' => 'Clean White']],
                        ['type' => 'note',  'x' => 20,  'y' => 180, 'w' => 300, 'h' => 140, 'data' => ['content' => "Mood: formal, celebratory, polished. Black-tie staging, elegant table settings."]],
                        ['type' => 'empty', 'x' => 340, 'y' => 180, 'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 580, 'y' => 20,  'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 20,  'y' => 340, 'w' => 220, 'h' => 220],
                        ['type' => 'empty', 'x' => 260, 'y' => 340, 'w' => 220, 'h' => 220],
                    ],
                ],
                [
                    'title' => 'Team Retreat Moodboard',
                    'description' => 'Starter layout for a company retreat or team offsite — swap in your own photos.',
                    'items' => [
                        ['type' => 'color', 'x' => 20,  'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#059669', 'name' => 'Retreat Green']],
                        ['type' => 'color', 'x' => 190, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#D97706', 'name' => 'Earthy Amber']],
                        ['type' => 'color', 'x' => 360, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#F5F5F4', 'name' => 'Neutral Stone']],
                        ['type' => 'note',  'x' => 20,  'y' => 180, 'w' => 300, 'h' => 140, 'data' => ['content' => "Mood: relaxed, collaborative, outdoorsy. Natural light, casual seating, open spaces."]],
                        ['type' => 'empty', 'x' => 340, 'y' => 180, 'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 580, 'y' => 20,  'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 20,  'y' => 340, 'w' => 220, 'h' => 220],
                        ['type' => 'empty', 'x' => 260, 'y' => 340, 'w' => 220, 'h' => 220],
                    ],
                ],
            ],

            'production' => [
                [
                    'title' => 'Film Shoot Moodboard',
                    'description' => 'Starter layout for a film or video production — swap in your own photos.',
                    'items' => [
                        ['type' => 'color', 'x' => 20,  'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#18181B', 'name' => 'Cinema Black']],
                        ['type' => 'color', 'x' => 190, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#78716C', 'name' => 'Grip Grey']],
                        ['type' => 'color', 'x' => 360, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#FDE047', 'name' => 'Practical Gold']],
                        ['type' => 'note',  'x' => 20,  'y' => 180, 'w' => 300, 'h' => 140, 'data' => ['content' => "Mood: cinematic, controlled lighting, atmospheric. Reference shot composition and set dressing."]],
                        ['type' => 'empty', 'x' => 340, 'y' => 180, 'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 580, 'y' => 20,  'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 20,  'y' => 340, 'w' => 220, 'h' => 220],
                        ['type' => 'empty', 'x' => 260, 'y' => 340, 'w' => 220, 'h' => 220],
                    ],
                ],
                [
                    'title' => 'Theatre Production Moodboard',
                    'description' => 'Starter layout for a stage or theatre production — swap in your own photos.',
                    'items' => [
                        ['type' => 'color', 'x' => 20,  'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#7C2D12', 'name' => 'Curtain Maroon']],
                        ['type' => 'color', 'x' => 190, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#EF4444', 'name' => 'Spotlight Red']],
                        ['type' => 'color', 'x' => 360, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#FDE047', 'name' => 'Stage Gold']],
                        ['type' => 'note',  'x' => 20,  'y' => 180, 'w' => 300, 'h' => 140, 'data' => ['content' => "Mood: dramatic, theatrical, bold contrast. Reference set design and lighting rigs."]],
                        ['type' => 'empty', 'x' => 340, 'y' => 180, 'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 580, 'y' => 20,  'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 20,  'y' => 340, 'w' => 220, 'h' => 220],
                        ['type' => 'empty', 'x' => 260, 'y' => 340, 'w' => 220, 'h' => 220],
                    ],
                ],
                [
                    'title' => 'Live Broadcast Moodboard',
                    'description' => 'Starter layout for a live broadcast or streamed production — swap in your own photos.',
                    'items' => [
                        ['type' => 'color', 'x' => 20,  'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#0F172A', 'name' => 'Broadcast Navy']],
                        ['type' => 'color', 'x' => 190, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#22D3EE', 'name' => 'Signal Cyan']],
                        ['type' => 'color', 'x' => 360, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#F1F5F9', 'name' => 'Studio Grey']],
                        ['type' => 'note',  'x' => 20,  'y' => 180, 'w' => 300, 'h' => 140, 'data' => ['content' => "Mood: crisp, technical, camera-ready. Clean backdrops, consistent lighting, on-screen graphics."]],
                        ['type' => 'empty', 'x' => 340, 'y' => 180, 'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 580, 'y' => 20,  'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 20,  'y' => 340, 'w' => 220, 'h' => 220],
                        ['type' => 'empty', 'x' => 260, 'y' => 340, 'w' => 220, 'h' => 220],
                    ],
                ],
            ],

            'church' => [
                [
                    'title' => 'Sunday Service Moodboard',
                    'description' => 'Starter layout for a regular church service — swap in your own photos.',
                    'items' => [
                        ['type' => 'color', 'x' => 20,  'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#FFFFFF', 'name' => 'Pure White']],
                        ['type' => 'color', 'x' => 190, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#B45309', 'name' => 'Warm Gold']],
                        ['type' => 'color', 'x' => 360, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#F5F5F4', 'name' => 'Soft Stone']],
                        ['type' => 'note',  'x' => 20,  'y' => 180, 'w' => 300, 'h' => 140, 'data' => ['content' => "Mood: welcoming, bright, uplifting. Natural light, simple staging, worship-focused."]],
                        ['type' => 'empty', 'x' => 340, 'y' => 180, 'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 580, 'y' => 20,  'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 20,  'y' => 340, 'w' => 220, 'h' => 220],
                        ['type' => 'empty', 'x' => 260, 'y' => 340, 'w' => 220, 'h' => 220],
                    ],
                ],
                [
                    'title' => 'Church Wedding / Dedication Moodboard',
                    'description' => 'Starter layout for a church wedding or child dedication — swap in your own photos.',
                    'items' => [
                        ['type' => 'color', 'x' => 20,  'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#FDF2F8', 'name' => 'Blush White']],
                        ['type' => 'color', 'x' => 190, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#D4AF37', 'name' => 'Sacred Gold']],
                        ['type' => 'color', 'x' => 360, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#7C2D12', 'name' => 'Deep Maroon']],
                        ['type' => 'note',  'x' => 20,  'y' => 180, 'w' => 300, 'h' => 140, 'data' => ['content' => "Mood: reverent, joyful, dignified. Soft floral accents, formal staging, gentle lighting."]],
                        ['type' => 'empty', 'x' => 340, 'y' => 180, 'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 580, 'y' => 20,  'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 20,  'y' => 340, 'w' => 220, 'h' => 220],
                        ['type' => 'empty', 'x' => 260, 'y' => 340, 'w' => 220, 'h' => 220],
                    ],
                ],
                [
                    'title' => 'Revival / Crusade Moodboard',
                    'description' => 'Starter layout for a large revival, crusade, or conference — swap in your own photos.',
                    'items' => [
                        ['type' => 'color', 'x' => 20,  'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#1E1B4B', 'name' => 'Night Indigo']],
                        ['type' => 'color', 'x' => 190, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#FDE047', 'name' => 'Radiant Gold']],
                        ['type' => 'color', 'x' => 360, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#FFFFFF', 'name' => 'Bright White']],
                        ['type' => 'note',  'x' => 20,  'y' => 180, 'w' => 300, 'h' => 140, 'data' => ['content' => "Mood: powerful, large-scale, uplifting. Bold stage lighting, banners, crowd energy."]],
                        ['type' => 'empty', 'x' => 340, 'y' => 180, 'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 580, 'y' => 20,  'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 20,  'y' => 340, 'w' => 220, 'h' => 220],
                        ['type' => 'empty', 'x' => 260, 'y' => 340, 'w' => 220, 'h' => 220],
                    ],
                ],
            ],

            'conference' => [
                [
                    'title' => 'Tech Summit Moodboard',
                    'description' => 'Starter layout for a technology summit or product conference — swap in your own photos.',
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
                [
                    'title' => 'Leadership Conference Moodboard',
                    'description' => 'Starter layout for an executive or leadership conference — swap in your own photos.',
                    'items' => [
                        ['type' => 'color', 'x' => 20,  'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#1C1917', 'name' => 'Executive Charcoal']],
                        ['type' => 'color', 'x' => 190, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#D4AF37', 'name' => 'Refined Gold']],
                        ['type' => 'color', 'x' => 360, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#FFFFFF', 'name' => 'Clean White']],
                        ['type' => 'note',  'x' => 20,  'y' => 180, 'w' => 300, 'h' => 140, 'data' => ['content' => "Mood: authoritative, refined, focused. Minimal staging, strong podium presence."]],
                        ['type' => 'empty', 'x' => 340, 'y' => 180, 'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 580, 'y' => 20,  'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 20,  'y' => 340, 'w' => 220, 'h' => 220],
                        ['type' => 'empty', 'x' => 260, 'y' => 340, 'w' => 220, 'h' => 220],
                    ],
                ],
                [
                    'title' => 'Workshop / Training Moodboard',
                    'description' => 'Starter layout for a hands-on workshop or training session — swap in your own photos.',
                    'items' => [
                        ['type' => 'color', 'x' => 20,  'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#059669', 'name' => 'Focus Green']],
                        ['type' => 'color', 'x' => 190, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#F97316', 'name' => 'Energy Orange']],
                        ['type' => 'color', 'x' => 360, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#F5F5F4', 'name' => 'Neutral Grey']],
                        ['type' => 'note',  'x' => 20,  'y' => 180, 'w' => 300, 'h' => 140, 'data' => ['content' => "Mood: engaging, hands-on, approachable. Round-table setups, breakout zones, natural light."]],
                        ['type' => 'empty', 'x' => 340, 'y' => 180, 'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 580, 'y' => 20,  'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 20,  'y' => 340, 'w' => 220, 'h' => 220],
                        ['type' => 'empty', 'x' => 260, 'y' => 340, 'w' => 220, 'h' => 220],
                    ],
                ],
            ],

            'entertainment' => [
                [
                    'title' => 'Concert Moodboard',
                    'description' => 'Starter layout for a concert or live music event — swap in your own photos.',
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
                [
                    'title' => 'Comedy Show Moodboard',
                    'description' => 'Starter layout for a comedy show or stand-up event — swap in your own photos.',
                    'items' => [
                        ['type' => 'color', 'x' => 20,  'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#18181B', 'name' => 'Club Black']],
                        ['type' => 'color', 'x' => 190, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#EAB308', 'name' => 'Spotlight Yellow']],
                        ['type' => 'color', 'x' => 360, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#DC2626', 'name' => 'Curtain Red']],
                        ['type' => 'note',  'x' => 20,  'y' => 180, 'w' => 300, 'h' => 140, 'data' => ['content' => "Mood: intimate, warm-lit, energetic. Single-spot staging, close audience seating."]],
                        ['type' => 'empty', 'x' => 340, 'y' => 180, 'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 580, 'y' => 20,  'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 20,  'y' => 340, 'w' => 220, 'h' => 220],
                        ['type' => 'empty', 'x' => 260, 'y' => 340, 'w' => 220, 'h' => 220],
                    ],
                ],
                [
                    'title' => 'Award Show Moodboard',
                    'description' => 'Starter layout for an award show or red-carpet event — swap in your own photos.',
                    'items' => [
                        ['type' => 'color', 'x' => 20,  'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#7C2D12', 'name' => 'Red Carpet']],
                        ['type' => 'color', 'x' => 190, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#D4AF37', 'name' => 'Award Gold']],
                        ['type' => 'color', 'x' => 360, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#000000', 'name' => 'Premiere Black']],
                        ['type' => 'note',  'x' => 20,  'y' => 180, 'w' => 300, 'h' => 140, 'data' => ['content' => "Mood: glamorous, high-profile, cinematic. Step-and-repeat backdrops, dramatic uplighting."]],
                        ['type' => 'empty', 'x' => 340, 'y' => 180, 'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 580, 'y' => 20,  'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 20,  'y' => 340, 'w' => 220, 'h' => 220],
                        ['type' => 'empty', 'x' => 260, 'y' => 340, 'w' => 220, 'h' => 220],
                    ],
                ],
            ],

            'exhibition' => [
                [
                    'title' => 'Trade Show Moodboard',
                    'description' => 'Starter layout for a trade show booth or expo presence — swap in your own photos.',
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
                [
                    'title' => 'Art Exhibition Moodboard',
                    'description' => 'Starter layout for an art or gallery exhibition — swap in your own photos.',
                    'items' => [
                        ['type' => 'color', 'x' => 20,  'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#FAFAF9', 'name' => 'Gallery White']],
                        ['type' => 'color', 'x' => 190, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#1C1917', 'name' => 'Frame Black']],
                        ['type' => 'color', 'x' => 360, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#B45309', 'name' => 'Accent Bronze']],
                        ['type' => 'note',  'x' => 20,  'y' => 180, 'w' => 300, 'h' => 140, 'data' => ['content' => "Mood: minimal, contemplative, curated. Soft gallery lighting, generous negative space."]],
                        ['type' => 'empty', 'x' => 340, 'y' => 180, 'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 580, 'y' => 20,  'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 20,  'y' => 340, 'w' => 220, 'h' => 220],
                        ['type' => 'empty', 'x' => 260, 'y' => 340, 'w' => 220, 'h' => 220],
                    ],
                ],
                [
                    'title' => 'Product Showcase Moodboard',
                    'description' => 'Starter layout for a product showcase or launch exhibition — swap in your own photos.',
                    'items' => [
                        ['type' => 'color', 'x' => 20,  'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#0F172A', 'name' => 'Premium Navy']],
                        ['type' => 'color', 'x' => 190, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#D4AF37', 'name' => 'Showcase Gold']],
                        ['type' => 'color', 'x' => 360, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#F8FAFC', 'name' => 'Display White']],
                        ['type' => 'note',  'x' => 20,  'y' => 180, 'w' => 300, 'h' => 140, 'data' => ['content' => "Mood: premium, focused, product-first. Pedestal displays, directional lighting."]],
                        ['type' => 'empty', 'x' => 340, 'y' => 180, 'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 580, 'y' => 20,  'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 20,  'y' => 340, 'w' => 220, 'h' => 220],
                        ['type' => 'empty', 'x' => 260, 'y' => 340, 'w' => 220, 'h' => 220],
                    ],
                ],
            ],

            'other' => [
                [
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
                [
                    'title' => 'Minimal Neutral Moodboard',
                    'description' => 'A clean, neutral starter layout for understated events — swap in your own photos.',
                    'items' => [
                        ['type' => 'color', 'x' => 20,  'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#E7E5E4', 'name' => 'Warm Grey']],
                        ['type' => 'color', 'x' => 190, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#57534E', 'name' => 'Charcoal Stone']],
                        ['type' => 'color', 'x' => 360, 'y' => 20,  'w' => 160, 'h' => 140, 'data' => ['value' => '#FAFAF9', 'name' => 'Off White']],
                        ['type' => 'note',  'x' => 20,  'y' => 180, 'w' => 300, 'h' => 140, 'data' => ['content' => "Mood: understated, versatile, calm. A quiet palette that works for almost any occasion."]],
                        ['type' => 'empty', 'x' => 340, 'y' => 180, 'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 580, 'y' => 20,  'w' => 220, 'h' => 260],
                        ['type' => 'empty', 'x' => 20,  'y' => 340, 'w' => 220, 'h' => 220],
                        ['type' => 'empty', 'x' => 260, 'y' => 340, 'w' => 220, 'h' => 220],
                    ],
                ],
            ],
        ];
    }
}