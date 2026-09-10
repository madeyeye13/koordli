{{-- resources/views/emails/platform-staff-invite.blade.php --}}
<p>Hi {{ $user->name }},</p>
<p>{{ $invitedByName }} has invited you to join the Koordli platform team as <strong>{{ str_replace('platform_', '', $user->role) }}</strong>.</p>
<p><a href="{{ url('/platform/accept-invite/' . $user->invite_token) }}">Accept invitation and set your password</a></p><p><a href="{{ route('platform.accept-invite', $user->invite_token) }}">Accept invitation and set your password</a></p>