<?php

namespace App\Livewire\Platform;

use App\Models\Central\Tenant;
use App\Models\Central\Plan;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.platform')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.platform.dashboard', [
            'totalTenants'  => Tenant::count(),
            'activeTenants' => Tenant::where('status', 'active')->count(),
            'trialTenants'  => Tenant::where('status', 'trial')->count(),
            'totalPlans'    => Plan::count(),
            'recentTenants' => Tenant::latest()->take(5)->get(),
        ]);
    }
}