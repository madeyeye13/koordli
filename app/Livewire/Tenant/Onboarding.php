<?php

namespace App\Livewire\Tenant;

use App\Models\Central\Plan;
use App\Traits\WithToast;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.tenant')]
class Onboarding extends Component
{
    use WithFileUploads, WithToast;

    public int  $step          = 1;
    public bool $needsPassword = true;
    public bool $needsPlan     = false;

    // Step 1 — Password
    public string $current_password          = '';
    public string $new_password              = '';
    public string $new_password_confirmation = '';
    public bool   $showCurrent               = false;
    public bool   $showNew                   = false;
    public bool   $showConfirm               = false;

    // Step 2 — Plan (only if no plan assigned)
    public ?int $selected_plan_id = null;

    // Step 3 — Branding
    public string $primary_color = '#7C3AED';
    public string $accent_color  = '#F59E0B';
    public $logo = null;

    public function mount(): void
    {
        $user   = auth()->user();
        $tenant = $user->tenant;

        // Self-registered users skip password step
        if ($user->is_self_registered) {
            $this->needsPassword = false;
        }

        // If no plan assigned, show plan selection step
        if (!$tenant->plan_id) {
            $this->needsPlan = true;
        }

        // Set starting step
        if ($this->needsPassword) {
            $this->step = 1; // password first
        } elseif ($this->needsPlan) {
            $this->step = 2; // plan next
        } else {
            $this->step = 3; // straight to branding
        }
    }

    // Step 1 — Change Password
    public function changePassword(): void
    {
        $this->validate([
            'current_password' => 'required',
            'new_password'     => [
                'required', 'min:8', 'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ],
        ], [
            'new_password.regex' => 'Password must contain uppercase, lowercase and a number.',
        ]);

        if (!Hash::check($this->current_password, auth()->user()->password)) {
            $this->addError('current_password', 'Current password is incorrect.');
            return;
        }

        auth()->user()->update(['password' => Hash::make($this->new_password)]);
        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->toastSuccess('Password updated successfully.');

        // Go to plan step or branding depending on what's needed
        $this->step = $this->needsPlan ? 2 : 3;
    }

    // Step 2 — Select Plan
    public function selectPlan(int $planId): void
    {
        $plan = Plan::find($planId);
        if (!$plan) return;

        $this->selected_plan_id = $planId;

        $tenant = auth()->user()->tenant;
        $tenant->update(['plan_id' => $planId]);

        DB::table('subscriptions')->insert([
            'tenant_id'            => $tenant->id,
            'plan_id'              => $planId,
            'status'               => 'trial',
            'trial_ends_at'        => now()->addDays($plan->trial_days ?? 30),
            'current_period_start' => now(),
            'current_period_end'   => now()->addDays($plan->trial_days ?? 30),
            'currency'             => $tenant->billing_currency ?? 'NGN',
            'amount'               => 0,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        $this->toastSuccess('Plan selected.');
        $this->step = 3;
    }

    // Step 3 — Branding
    public function saveBranding(): void
    {
        $this->validate([
            'primary_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'accent_color'  => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'logo'          => 'nullable|image|max:2048',
        ]);

        $tenant   = auth()->user()->tenant;
        $branding = $tenant->branding ?? [];

        $branding['primary_color'] = $this->primary_color;
        $branding['accent_color']  = $this->accent_color;

        if ($this->logo) {
            $path = $this->logo->store('tenants/' . $tenant->id . '/branding', 'public');
            $branding['logo'] = $path;
        }

        $tenant->update(['branding' => $branding]);
        $this->toastSuccess('Branding saved.');
        $this->step = 4;
    }

    public function skipBranding(): void
    {
        $this->step = 4;
    }

    public function goToDashboard(): void
    {
        auth()->user()->update([
            'onboarding_completed'    => true,
            'onboarding_completed_at' => now(),
        ]);

        $this->redirect(route('tenant.dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.tenant.onboarding', [
            'plans' => $this->needsPlan ? Plan::where('is_active', true)->get() : collect(),
        ]);
    }
}