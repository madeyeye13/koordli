{{-- resources/views/emails/wish-approved.blade.php --}}
<p>Hi {{ $wish->guest_name }},</p>
<p>Your wish is now live on the event page:</p>
<blockquote>{{ $wish->message }}</blockquote>