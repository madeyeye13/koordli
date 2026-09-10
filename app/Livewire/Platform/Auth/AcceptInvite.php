<?php

namespace App\Livewire\Platform\Auth;

use App\Models\Central\PlatformUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class AcceptInvite extends Component
{
    public string $token;
    public ?PlatformUser $user = null;

    public string $newPassword = '';
    public string $newPasswordConfirm = '';
    public string $error = '';

    private const INVITE_EXPIRY_DAYS = 7;

    public function mount(string $token): void
    {
        $this->token = $token;

        // Fetch all pending invites and compare with hash_equals — avoids
        // a raw WHERE token = ? timing side-channel on a security token.
        $candidates = PlatformUser::whereNull('invite_accepted_at')->whereNotNull('invite_token')->get();

        foreach ($candidates as $candidate) {
            if (hash_equals($candidate->invite_token, $token)) {
                $this->user = $candidate;
                break;
            }
        }

        if (!$this->user) {
            $this->error = 'This invitation link is invalid or has already been used.';
            return;
        }

        if ($this->user->invited_at && now()->diffInDays($this->user->invited_at) > self::INVITE_EXPIRY_DAYS) {
            $this->error = 'This invitation has expired. Please ask an admin to send a new one.';
            $this->user = null;
        }
    }

    public function acceptInvite(): void
    {
        if (!$this->user) return;

        $this->validate([
            'newPassword' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ], [], ['newPassword' => 'password']);

        $this->user->update([
            'password'           => $this->newPassword,
            'invite_accepted_at' => now(),
            'invite_token'       => null, // burned — cannot be reused
        ]);

        auth('platform')->login($this->user);
        $this->redirect(route('platform.dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.platform.auth.accept-invite');
    }
}