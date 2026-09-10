{{-- resources/views/emails/new-wish.blade.php --}}
<p>Hi {{ $recipientName }},</p>
<p><strong>{{ $wish->guest_name }}</strong> left a wish for {{ $wish->event->name ?? 'your event' }}:</p>
<blockquote>{{ $wish->message }}</blockquote>
@if($wish->status === 'pending')
<p>This wish is awaiting approval before it appears publicly.</p>
@endif