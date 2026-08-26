<?php

namespace App\Livewire\Public;

use App\Models\Tenant\QuickAccessLink;
use App\Services\QuickAccessService;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.rsvp')]
class QuickAccessPage extends Component
{
    use WithToast;

    public ?QuickAccessLink $link = null;
    public bool $isValid       = false;
    public bool $needsPinSetup = false;
    public bool $pinVerified   = false;

    public string $pinInput   = '';
    public string $pinConfirm = '';
    public string $pinError   = '';

    public function mount(string $token): void
    {
        $this->link    = app(QuickAccessService::class)->findValidLink($token);
        $this->isValid = (bool) $this->link;

        if (!$this->isValid) return;

        $this->needsPinSetup = $this->link->needsPinSetup();
        $this->pinVerified   = !$this->link->pin_enabled
            || session()->get('qa_pin_verified_' . $this->link->id, false);
    }

    public function setPin(): void
    {
        $this->validate([
            'pinInput'   => 'required|digits:4',
            'pinConfirm' => 'required|same:pinInput',
        ], [], ['pinInput' => 'PIN', 'pinConfirm' => 'PIN confirmation']);

        $this->link->setPin($this->pinInput);
        $this->needsPinSetup = false;
        $this->pinVerified   = true;
        session()->put('qa_pin_verified_' . $this->link->id, true);
    }

    public function verifyPin(): void
    {
        if (!$this->link->verifyPin($this->pinInput)) {
            $this->pinError = 'Incorrect PIN.';
            $this->pinInput = '';
            return;
        }

        $this->pinVerified = true;
        $this->pinError    = '';
        session()->put('qa_pin_verified_' . $this->link->id, true);
    }

    public function render()
    {
        $payloadJson = null;

        // Task/event data is only ever embedded AFTER PIN verification —
        // if it were embedded earlier, anyone with the raw link could view
        // page source and see task titles/event names without knowing the
        // PIN, defeating part of its purpose as an extra protection layer.
        if ($this->isValid && $this->pinVerified) {
            $service = app(QuickAccessService::class);
            $menu    = $service->menuFor($this->link);

            $eventsByAction     = [];
            $itemsByActionEvent = [];

            foreach ($menu as $item) {
                $key    = $item['key'];
                $events = $service->eventsFor($this->link, $key);
                $eventsByAction[$key] = $events->map(fn($e) => ['id' => $e->id, 'name' => $e->name])->values();

                foreach ($events as $event) {
                    if ($key === 'update_task') {
                        $items = $service->tasksFor($this->link, $event->id);
                        $itemsByActionEvent["{$key}:" . ($event->id ?? 'null')] = $items->map(fn($t) => [
                            'id'       => $t->id,
                            'title'    => $t->title,
                            'status'   => $t->status->value,
                            'due_date' => $t->due_date?->format('D, d M Y'),
                        ])->values();
                    } elseif ($key === 'update_runsheet') {
                        $items = $service->runsheetItemsFor($this->link, $event->id);
                        $itemsByActionEvent["{$key}:{$event->id}"] = $items->map(fn($i) => [
                            'id'         => $i->id,
                            'title'      => $i->title,
                            'status'     => $i->status->value,
                            'start_time' => $i->start_time?->format('g:i A'),
                        ])->values();
                    }
                }
            }

            $payloadJson = json_encode([
                'personName'         => app(QuickAccessService::class)->personName($this->link),
                'menu'                => $menu,
                'eventsByAction'      => $eventsByAction,
                'itemsByActionEvent'  => $itemsByActionEvent,
                'token'               => $this->link->token,
            ]);
        }

        return view('livewire.public.quick-access-page', ['payloadJson' => $payloadJson])
            ->layout('layouts.rsvp', ['title' => 'Quick Access']);
    }
}