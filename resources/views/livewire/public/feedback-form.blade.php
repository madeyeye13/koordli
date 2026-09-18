<div class="feedback-page">
    <style>
        .feedback-page { max-width: 760px; margin: 0 auto; padding: 56px 20px 80px; color: var(--lp-text, #1C1917); }
        .feedback-intro { text-align: center; max-width: 620px; margin: 0 auto 40px; }
        .feedback-kicker { color: #7C3AED; font-size: 12px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; margin-bottom: 12px; }
        .feedback-title { font-size: clamp(30px, 5vw, 46px); line-height: 1.1; margin: 0 0 14px; }
        .feedback-lead { color: var(--lp-text-muted, #78716C); font-size: 16px; line-height: 1.7; }
        .feedback-section { background: var(--lp-surface, #fff); border: 1px solid var(--lp-border, #E7E5E4); border-radius: 12px; padding: 28px; margin-bottom: 16px; }
        .feedback-section h2 { font-size: 18px; margin: 0 0 20px; }
        .feedback-field { margin-bottom: 22px; }
        .feedback-field:last-child { margin-bottom: 0; }
        .feedback-label { display: block; font-size: 14px; font-weight: 600; margin-bottom: 9px; }
        .feedback-required { color: #DC2626; }
        .feedback-input, .feedback-textarea { width: 100%; box-sizing: border-box; border: 1px solid #D6D3D1; border-radius: 8px; padding: 12px 13px; font: inherit; color: inherit; background: var(--lp-bg, #fff); }
        .feedback-page input[type="radio"], .feedback-page input[type="checkbox"] { accent-color: #7C3AED; width: 16px; height: 16px; margin: 1px 0 0; flex-shrink: 0; }
        .feedback-textarea { min-height: 112px; resize: vertical; }
        .feedback-options { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
        .feedback-option { display: flex; align-items: flex-start; gap: 8px; padding: 11px 12px; border: 1px solid #E7E5E4; border-radius: 8px; font-size: 14px; cursor: pointer; }
        .feedback-scale { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 8px; }
        .feedback-scale label { display: flex; flex-direction: column; align-items: center; gap: 5px; padding: 10px 5px; border: 1px solid #E7E5E4; border-radius: 8px; font-size: 12px; cursor: pointer; text-align: center; }
        .feedback-error { color: #DC2626; font-size: 12px; margin-top: 6px; }
        .feedback-submit { width: 100%; border: 0; border-radius: 8px; padding: 14px 18px; background: #7C3AED; color: #fff; font: inherit; font-weight: 700; cursor: pointer; transition: opacity 150ms ease; }
        .feedback-submit.feedback-submit-loading { opacity: .5; cursor: wait; }
        @media (max-width: 520px) { .feedback-page { padding: 36px 16px 60px; } .feedback-section { padding: 20px 16px; } .feedback-options { grid-template-columns: 1fr; } .feedback-scale label { font-size: 11px; } }
    </style>

    <div class="feedback-intro">
        <div class="feedback-kicker">Koordli tester feedback</div>
        <h1 class="feedback-title">Help us improve Koordli</h1>
        <p class="feedback-lead">Thanks for testing Koordli 👋 This short form takes less than two minutes. Your feedback will help us make Koordli more useful for event professionals.</p>
    </div>

    <form wire:submit="submit">
        <section class="feedback-section">
            <h2>About you</h2>
            <div class="feedback-field">
                <label class="feedback-label">What's your name? <span class="feedback-required">*</span></label>
                <input class="feedback-input" type="text" wire:model="name" autocomplete="name">
                @error('name') <div class="feedback-error">{{ $message }}</div> @enderror
            </div>
            <div class="feedback-field">
                <label class="feedback-label">What best describes your experience with event management? <span class="feedback-required">*</span></label>
                <div class="feedback-options">
                    @foreach(['event_planner' => 'Event Planner / Coordinator', 'wedding_planner' => 'Wedding Planner', 'event_company' => 'Event Company', 'corporate' => 'Corporate / Organisation', 'church' => 'Church / Ministry', 'other' => 'Other'] as $value => $label)
                    <label class="feedback-option"><input type="radio" wire:model="experience" value="{{ $value }}"> {{ $label }}</label>
                    @endforeach
                </div>
                @error('experience') <div class="feedback-error">{{ $message }}</div> @enderror
            </div>
        </section>

        <section class="feedback-section">
            <h2>Your experience</h2>
            @foreach([
                'navigationRating' => ['How easy was Koordli to navigate?', ['Very difficult', 'Difficult', 'Okay', 'Easy', 'Very easy']],
                'clarityRating' => ['How clear was it what Koordli is designed to help you do?', ['Not clear at all', 'Slightly unclear', 'Somewhat clear', 'Clear', 'Very clear']],
            ] as $field => [$question, $labels])
            <div class="feedback-field">
                <label class="feedback-label">{{ $question }} <span class="feedback-required">*</span></label>
                <div class="feedback-scale">
                    @foreach($labels as $index => $label)
                    <label><input type="radio" wire:model="{{ $field }}" value="{{ $index + 1 }}"><strong>{{ $index + 1 }}</strong><span>{{ $label }}</span></label>
                    @endforeach
                </div>
                @error($field) <div class="feedback-error">{{ $message }}</div> @enderror
            </div>
            @endforeach

            <div class="feedback-field">
                <label class="feedback-label">Which part of Koordli did you find most useful? <span class="feedback-required">*</span></label>
                <div class="feedback-options">
                    @foreach(['events' => 'Events', 'tasks' => 'Tasks', 'vendors' => 'Vendors', 'budget' => 'Budget', 'contracts' => 'Contracts', 'rsvp' => 'RSVP / Check-in', 'runsheet' => 'Runsheet', 'client_portal' => 'Client Portal', 'bookings' => 'Bookings', 'other' => 'Other'] as $value => $label)
                    <label class="feedback-option"><input type="checkbox" wire:model="mostUseful" value="{{ $value }}"> {{ $label }}</label>
                    @endforeach
                </div>
                @error('mostUseful') <div class="feedback-error">{{ $message }}</div> @enderror
            </div>

            <div class="feedback-field">
                <label class="feedback-label">Did anything confuse you or not work as expected? <span class="feedback-required">*</span></label>
                <div class="feedback-options">
                    <label class="feedback-option"><input type="radio" wire:model="hadConfusion" value="0"> No</label>
                    <label class="feedback-option"><input type="radio" wire:model="hadConfusion" value="1"> Yes</label>
                </div>
                @error('hadConfusion') <div class="feedback-error">{{ $message }}</div> @enderror
            </div>

            @if($hadConfusion === '1')
            <div class="feedback-field">
                <label class="feedback-label">If yes, what happened?</label>
                <textarea class="feedback-textarea" wire:model="confusionDetails"></textarea>
                @error('confusionDetails') <div class="feedback-error">{{ $message }}</div> @enderror
            </div>
            @endif

            <div class="feedback-field">
                <label class="feedback-label">What is the one thing you would improve about Koordli? <span class="feedback-required">*</span></label>
                <textarea class="feedback-textarea" wire:model="improvement"></textarea>
                @error('improvement') <div class="feedback-error">{{ $message }}</div> @enderror
            </div>

            <div class="feedback-field">
                <label class="feedback-label">How likely are you to use Koordli for a real event? <span class="feedback-required">*</span></label>
                <div class="feedback-scale">
                    @foreach(['Not likely', 'Unlikely', 'Maybe', 'Likely', 'Very likely'] as $index => $label)
                    <label><input type="radio" wire:model="likelihood" value="{{ $index + 1 }}"><strong>{{ $index + 1 }}</strong><span>{{ $label }}</span></label>
                    @endforeach
                </div>
                @error('likelihood') <div class="feedback-error">{{ $message }}</div> @enderror
            </div>
        </section>

        <section class="feedback-section">
            <h2>Your overall rating</h2>
            <div class="feedback-field">
                <label class="feedback-label">How would you rate your overall experience with Koordli? <span class="feedback-required">*</span></label>
                <div class="feedback-scale">
                    @foreach(range(1, 5) as $rating)
                    <label><input type="radio" wire:model="overallRating" value="{{ $rating }}"><strong>{{ str_repeat('★', $rating) }}</strong><span>{{ $rating }}/5</span></label>
                    @endforeach
                </div>
                @error('overallRating') <div class="feedback-error">{{ $message }}</div> @enderror
            </div>
        </section>

        <button class="feedback-submit" type="submit" wire:loading.attr="disabled" wire:loading.class="feedback-submit-loading" wire:target="submit">Submit feedback</button>
    </form>
</div>
