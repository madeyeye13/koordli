<div x-data="{ selectedFeedback: null, confirmDelete: false }">
    <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:24px;">
        <div>
            <div class="krd-label" style="margin-bottom:4px;">Product research</div>
            <h2 class="krd-heading-3" style="color:#1C1917;">Tester Feedback</h2>
            <p style="font-size:13px;color:#78716C;margin-top:4px;">Review feedback submitted through your public tester survey.</p>
        </div>
        <div style="font-size:13px;color:#78716C;">{{ $submissions->count() }} {{ Str::plural('response', $submissions->count()) }}</div>
    </div>

    <div class="krd-card" style="padding:16px;margin-bottom:16px;">
        <input class="krd-input" type="search" wire:model.live.debounce.300ms="search" placeholder="Search by tester name...">
    </div>

    <div class="krd-card" style="padding:0;overflow:hidden;">
        <div class="krd-table-wrap">
            <table class="krd-table">
                <thead><tr><th>Tester</th><th>Experience</th><th>Overall</th><th>Likelihood</th><th>Submitted</th><th></th></tr></thead>
                <tbody>
                @forelse($submissions as $submission)
                    <tr>
                        <td style="font-weight:600;">{{ $submission->name }}</td>
                        <td>{{ Str::headline($submission->experience) }}</td>
                        <td style="color:#F59E0B;">{{ str_repeat('★', $submission->overall_rating) }} <span style="color:#78716C;">({{ $submission->overall_rating }}/5)</span></td>
                        <td>{{ $submission->likelihood }}/5</td>
                        <td style="font-size:12px;color:#78716C;">{{ $submission->created_at->format('M d, Y g:i A') }}</td>
                        <td>
                            <button
                                class="krd-btn krd-btn-secondary krd-btn-sm"
                                type="button"
                                x-on:click="selectedFeedback = {{ Js::from([
                                    'name' => $submission->name,
                                    'id' => $submission->id,
                                    'experience' => Str::headline($submission->experience),
                                    'navigation' => $submission->navigation_rating . '/5',
                                    'clarity' => $submission->clarity_rating . '/5',
                                    'mostUseful' => collect($submission->most_useful ?? [])->map(fn ($feature) => Str::headline($feature))->join(', '),
                                    'likelihood' => $submission->likelihood . '/5',
                                    'overall' => $submission->overall_rating . '/5',
                                    'confusion' => $submission->confusion_details,
                                    'improvement' => $submission->improvement,
                                ]) }}"
                            >View</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;color:#A8A29E;padding:36px;">No feedback responses yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <template x-teleport="body">
    <div x-cloak x-show="selectedFeedback" x-on:click.self="selectedFeedback = null" x-transition.opacity style="position:fixed;left:0;top:0;width:100vw;height:100vh;z-index:70;padding:20px;">
        <div style="position:absolute;inset:0;background:rgba(28,25,23,.45);" x-on:click="selectedFeedback = null"></div>
        <div class="krd-card" style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:calc(100% - 40px);max-width:680px;max-height:90vh;overflow-y:auto;">
            <div style="display:flex;justify-content:space-between;gap:16px;align-items:flex-start;margin-bottom:20px;">
                <div><div class="krd-label">Tester response</div><h3 x-text="selectedFeedback?.name" style="font-size:22px;color:#1C1917;margin-top:4px;"></h3></div>
                <button type="button" class="krd-btn krd-btn-ghost" x-on:click="selectedFeedback = null; confirmDelete = false">Close</button>
            </div>
            <div style="display:flex;justify-content:space-between;gap:20px;padding:10px 0;border-bottom:1px solid #E7E5E4;font-size:13px;"><span style="color:#78716C;">Experience</span><strong x-text="selectedFeedback?.experience" style="text-align:right;"></strong></div>
            <div style="display:flex;justify-content:space-between;gap:20px;padding:10px 0;border-bottom:1px solid #E7E5E4;font-size:13px;"><span style="color:#78716C;">Navigation</span><strong x-text="selectedFeedback?.navigation" style="text-align:right;"></strong></div>
            <div style="display:flex;justify-content:space-between;gap:20px;padding:10px 0;border-bottom:1px solid #E7E5E4;font-size:13px;"><span style="color:#78716C;">Clarity</span><strong x-text="selectedFeedback?.clarity" style="text-align:right;"></strong></div>
            <div style="display:flex;justify-content:space-between;gap:20px;padding:10px 0;border-bottom:1px solid #E7E5E4;font-size:13px;"><span style="color:#78716C;">Most useful</span><strong x-text="selectedFeedback?.mostUseful" style="text-align:right;"></strong></div>
            <div style="display:flex;justify-content:space-between;gap:20px;padding:10px 0;border-bottom:1px solid #E7E5E4;font-size:13px;"><span style="color:#78716C;">Would use for a real event</span><strong x-text="selectedFeedback?.likelihood" style="text-align:right;"></strong></div>
            <div style="display:flex;justify-content:space-between;gap:20px;padding:10px 0;border-bottom:1px solid #E7E5E4;font-size:13px;"><span style="color:#78716C;">Overall rating</span><strong x-text="selectedFeedback?.overall" style="text-align:right;"></strong></div>
            <div x-show="selectedFeedback?.confusion" style="margin-top:20px;"><div class="krd-label" style="margin-bottom:6px;">What confused them</div><div x-text="selectedFeedback?.confusion" style="font-size:14px;line-height:1.7;white-space:pre-wrap;"></div></div>
            <div style="margin-top:20px;"><div class="krd-label" style="margin-bottom:6px;">Suggested improvement</div><div x-text="selectedFeedback?.improvement" style="font-size:14px;line-height:1.7;white-space:pre-wrap;"></div></div>
            <div style="display:flex;justify-content:flex-end;margin-top:24px;padding-top:16px;border-top:1px solid #E7E5E4;">
                <button type="button" class="krd-btn" style="background:#FEE2E2;color:#DC2626;border-color:#FECACA;" x-on:click="confirmDelete = true">Delete feedback</button>
            </div>
        </div>
    </div>
    </template>

    <template x-teleport="body">
        <div x-cloak x-show="confirmDelete" style="position:fixed;left:0;top:0;width:100vw;height:100vh;z-index:100;padding:20px;" x-transition.opacity>
            <div style="position:absolute;inset:0;background:rgba(28,25,23,0.42);" x-on:click="confirmDelete = false"></div>
            <div style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:100%;max-width:360px;background:#fff;border:1px solid #E7E5E4;border-radius:10px;padding:24px;box-shadow:0 20px 50px rgba(28,25,23,0.2);" x-transition>
                <div style="width:38px;height:38px;border-radius:50%;background:#FEE2E2;color:#DC2626;display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v5M14 11v5"/></svg>
                </div>
                <h3 style="font-size:16px;font-weight:600;color:#1C1917;margin-bottom:6px;">Delete this feedback?</h3>
                <p style="font-size:13px;line-height:1.6;color:#78716C;margin-bottom:20px;">This response will be permanently removed from the platform dashboard.</p>
                <div style="display:flex;gap:10px;justify-content:flex-end;">
                    <button type="button" class="krd-btn krd-btn-secondary" x-on:click="confirmDelete = false">Cancel</button>
                    <button type="button" class="krd-btn" style="background:#DC2626;color:#fff;border-color:#DC2626;" x-on:click="$wire.deleteFeedback(selectedFeedback.id); confirmDelete = false; selectedFeedback = null">Delete feedback</button>
                </div>
            </div>
        </div>
    </template>
</div>
