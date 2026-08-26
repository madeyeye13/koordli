@if(!$isValid)
<div style="max-width:440px;margin:0 auto;padding:60px 20px;text-align:center;min-height:100vh;display:flex;flex-direction:column;justify-content:center;">
    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="#D6D3D1" stroke-width="1.5" style="margin:0 auto 16px;">
        <rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
    </svg>
    <h2 style="font-size:18px;font-weight:600;color:#1C1917;margin-bottom:8px;">This link is no longer valid</h2>
    <p style="font-size:13px;color:#78716C;">Please contact your event organizer for a new link.</p>
</div>

@elseif($needsPinSetup)
<div style="max-width:400px;margin:0 auto;padding:60px 20px;min-height:100vh;display:flex;flex-direction:column;justify-content:center;">
    <div style="text-align:center;margin-bottom:24px;">
        <div style="width:52px;height:52px;border-radius:50%;background:#F5F3FF;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#7C3AED" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
        </div>
        <h2 style="font-size:17px;font-weight:600;color:#1C1917;">Set a PIN</h2>
        <p style="font-size:12px;color:#78716C;margin-top:6px;line-height:1.6;">Your organizer requires a PIN to protect this link. You'll enter it each time you visit.</p>
    </div>
    <div class="krd-card" style="padding:24px;">
        <input wire:model="pinInput" type="password" inputmode="numeric" maxlength="4" placeholder="Enter 4-digit PIN" class="krd-input" style="text-align:center;font-size:22px;letter-spacing:10px;margin-bottom:10px;">
        @error('pinInput') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
        <input wire:model="pinConfirm" type="password" inputmode="numeric" maxlength="4" placeholder="Confirm PIN" class="krd-input" style="text-align:center;font-size:22px;letter-spacing:10px;margin-bottom:16px;">
        @error('pinConfirm') <span class="krd-input-error-msg">{{ $message }}</span> @enderror
        <button wire:click="setPin" class="krd-btn krd-btn-primary" style="width:100%;">Set PIN</button>
    </div>
</div>

@elseif(!$pinVerified)
<div style="max-width:400px;margin:0 auto;padding:60px 20px;min-height:100vh;display:flex;flex-direction:column;justify-content:center;">
    <div style="text-align:center;margin-bottom:24px;">
        <div style="width:52px;height:52px;border-radius:50%;background:#F5F3FF;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#7C3AED" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
        </div>
        <h2 style="font-size:17px;font-weight:600;color:#1C1917;">Enter your PIN</h2>
    </div>
    <div class="krd-card" style="padding:24px;">
        <input wire:model="pinInput" wire:keydown.enter="verifyPin" type="password" inputmode="numeric" maxlength="4" placeholder="••••" class="krd-input" style="text-align:center;font-size:22px;letter-spacing:10px;margin-bottom:10px;">
        @if($pinError)<div style="color:#DC2626;font-size:12px;text-align:center;margin-bottom:10px;">{{ $pinError }}</div>@endif
        <button wire:click="verifyPin" class="krd-btn krd-btn-primary" style="width:100%;">Continue</button>
    </div>
</div>

@else
<div x-data="quickAccessApp()" x-init="init()" style="max-width:520px;margin:0 auto;padding:24px 16px 60px;min-height:100vh;display:flex;flex-direction:column;justify-content:center;">

    <div style="text-align:center;margin-bottom:28px;padding-top:8px;">
        <div style="width:52px;height:52px;border-radius:50%;background:#F5F3FF;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#7C3AED" stroke-width="2"><path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/></svg>
        </div>
        <h2 x-show="view === 'menu'" style="font-size:19px;font-weight:700;color:#1C1917;line-height:1.4;">Hi {{ auth()->check() ? '' : '' }}<span x-text="personName"></span>, what would you like to do?</h2>
        <h2 x-show="view !== 'menu'" x-text="screenTitle" style="font-size:17px;font-weight:700;color:#1C1917;"></h2>
    </div>

    <div x-show="view !== 'menu'" style="margin-bottom:16px;">
        <button x-on:click="goBack()" style="background:none;border:none;color:#7C3AED;font-size:13px;cursor:pointer;display:flex;align-items:center;gap:4px;padding:0;">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Back
        </button>
    </div>

    <template x-if="view === 'menu'">
        <div style="display:flex;flex-direction:column;gap:10px;">
            <template x-for="item in menu" :key="item.key">
                <button x-on:click="pickAction(item.key)" class="krd-card" style="padding:18px;text-align:left;display:flex;align-items:center;justify-content:space-between;cursor:pointer;border:1px solid #E7E5E4;background:#fff;">
                    <span style="font-size:14px;font-weight:600;color:#1C1917;" x-text="item.label"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#A8A29E" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </template>
        </div>
    </template>

    <template x-if="view === 'events'">
        <div style="display:flex;flex-direction:column;gap:10px;">
            <template x-if="currentEvents.length === 0">
                <div class="krd-card" style="padding:32px;text-align:center;color:#A8A29E;font-size:13px;">Nothing here yet.</div>
            </template>
            <template x-for="ev in currentEvents" :key="ev.id ?? 'general'">
                <button x-on:click="pickEvent(ev)" class="krd-card" style="padding:16px;text-align:left;display:flex;align-items:center;justify-content:space-between;cursor:pointer;border:1px solid #E7E5E4;background:#fff;">
                    <span style="font-size:13.5px;font-weight:600;color:#1C1917;" x-text="ev.name"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#A8A29E" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </template>
        </div>
    </template>

    <template x-if="view === 'items'">
        <div style="display:flex;flex-direction:column;gap:10px;">
            <template x-if="currentItems.length === 0">
                <div class="krd-card" style="padding:32px;text-align:center;color:#A8A29E;font-size:13px;">Nothing here yet.</div>
            </template>
            <template x-for="it in currentItems" :key="it.id">
                <div class="krd-card" style="padding:16px;">
                    <div style="font-size:13.5px;font-weight:600;color:#1C1917;margin-bottom:2px;" x-text="it.title"></div>
                    <div style="font-size:11px;color:#A8A29E;margin-bottom:12px;" x-text="it.due_date || it.start_time || ''"></div>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                        <template x-for="opt in statusOptions" :key="opt.value">
                            <button x-on:click="setStatus(it, opt.value)"
                                :style="it.status === opt.value
                                    ? `padding:6px 12px;border-radius:20px;font-size:11.5px;font-weight:600;border:1.5px solid ${opt.color};background:${opt.color}18;color:${opt.color};cursor:pointer;`
                                    : 'padding:6px 12px;border-radius:20px;font-size:11.5px;font-weight:500;border:1.5px solid #E7E5E4;background:#fff;color:#78716C;cursor:pointer;'"
                                x-text="opt.label"></button>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </template>

    <template x-if="view === 'delay_note'">
        <div class="krd-card" style="padding:20px;">
            <div style="font-size:13px;font-weight:600;color:#1C1917;margin-bottom:4px;" x-text="delayingItem?.title"></div>
            <p style="font-size:12px;color:#78716C;margin-bottom:12px;">Add a quick note about the delay (optional).</p>
            <textarea x-model="delayNote" rows="3" placeholder="e.g. Running 20 minutes behind, traffic..." class="krd-input" style="margin-bottom:14px;"></textarea>
            <div style="display:flex;gap:10px;">
                <button x-on:click="confirmDelay()" class="krd-btn krd-btn-primary" style="flex:1;">Mark as Delayed</button>
                <button x-on:click="view = 'items'" class="krd-btn krd-btn-ghost" style="flex:1;">Cancel</button>
            </div>
        </div>
    </template>

    <div style="margin-top:32px;padding:14px 16px;background:#F5F3FF;border-radius:8px;text-align:center;">
        <p style="font-size:11.5px;color:#7C3AED;line-height:1.6;">💡 This link is yours to reuse anytime — bookmark it, and come back whenever you need to make another update.</p>
    </div>

    <div x-show="toast.show" x-transition style="position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:#1C1917;color:#fff;padding:10px 18px;border-radius:8px;font-size:12.5px;z-index:999;" x-text="toast.message"></div>
</div>

<script type="application/json" id="qa-payload">{!! $payloadJson !!}</script>

<script>
function quickAccessApp() {
    return {
        personName: '', menu: [], eventsByAction: {}, itemsByActionEvent: {}, token: '',
        view: 'menu', currentAction: null, currentEventId: undefined,
        currentEvents: [], currentItems: [], delayingItem: null, delayNote: '',
        toast: { show: false, message: '' },

        statusOptionsByAction: {
            update_task: [
                { value: 'todo', label: 'To Do', color: '#78716C' },
                { value: 'in_progress', label: 'In Progress', color: '#F59E0B' },
                { value: 'blocked', label: 'Blocked', color: '#EF4444' },
                { value: 'done', label: 'Done', color: '#10B981' },
                { value: 'cancelled', label: 'Cancelled', color: '#A8A29E' },
            ],
            update_runsheet: [
                { value: 'pending', label: 'Pending', color: '#78716C' },
                { value: 'in_progress', label: 'In Progress', color: '#F59E0B' },
                { value: 'done', label: 'Done', color: '#10B981' },
                { value: 'delayed', label: 'Delayed', color: '#EF4444' },
            ],
        },

        get statusOptions() { return this.statusOptionsByAction[this.currentAction] || []; },
        get screenTitle() {
            if (this.view === 'events') return this.menu.find(m => m.key === this.currentAction)?.label || '';
            if (this.view === 'items') return this.currentEvents.find(e => (e.id ?? null) === this.currentEventId)?.name || '';
            if (this.view === 'delay_note') return 'Add a delay note';
            return '';
        },

        init() {
            const data = JSON.parse(document.getElementById('qa-payload').textContent);
            this.personName = data.personName;
            this.menu = data.menu;
            this.eventsByAction = data.eventsByAction;
            this.itemsByActionEvent = data.itemsByActionEvent;
            this.token = data.token;
        },

        pickAction(key) {
            this.currentAction = key;
            this.currentEvents = this.eventsByAction[key] || [];
            this.view = 'events';
        },

        pickEvent(ev) {
            this.currentEventId = ev.id ?? null;
            const itemKey = `${this.currentAction}:${this.currentEventId ?? 'null'}`;
            this.currentItems = JSON.parse(JSON.stringify(this.itemsByActionEvent[itemKey] || []));
            this.view = 'items';
        },

        goBack() {
            if (this.view === 'items') { this.view = 'events'; return; }
            if (this.view === 'events') { this.view = 'menu'; return; }
            if (this.view === 'delay_note') { this.view = 'items'; return; }
            this.view = 'menu';
        },

        setStatus(item, status) {
            if (status === 'delayed') {
                this.delayingItem = item;
                this.delayNote = '';
                this.view = 'delay_note';
                return;
            }
            this.commitStatus(item, status, null);
        },

        confirmDelay() {
            this.commitStatus(this.delayingItem, 'delayed', this.delayNote);
            this.view = 'items';
        },

        commitStatus(item, status, delayNote) {
            const previousStatus = item.status;
            item.status = status; // optimistic — instant, no wait for the server

            const endpoint = this.currentAction === 'update_task'
                ? `/quick-access/${this.token}/update-task`
                : `/quick-access/${this.token}/update-runsheet`;
            const body = this.currentAction === 'update_task'
                ? { task_id: item.id, status: status }
                : { item_id: item.id, status: status, delay_note: delayNote };

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify(body),
            })
            .then(res => { if (!res.ok) throw new Error(); this.showToast('Updated ✓'); })
            .catch(() => { item.status = previousStatus; this.showToast('Could not save — please try again.'); });
        },

        showToast(message) {
            this.toast = { show: true, message };
            setTimeout(() => { this.toast.show = false; }, 2500);
        },
    };
}
</script>
@endif