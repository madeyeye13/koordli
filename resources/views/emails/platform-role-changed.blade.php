{{-- resources/views/emails/platform-role-changed.blade.php --}}
<p>Hi {{ $user->name }},</p>
<p>Your role on the Koordli platform team has been changed to <strong>{{ str_replace('platform_', '', $newRole) }}</strong>.</p>