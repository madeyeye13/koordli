{{-- resources/views/emails/platform-password-reset-code.blade.php --}}
<p>Hi {{ $name }},</p>
<p>Your verification code is:</p>
<h2 style="letter-spacing:4px;">{{ $code }}</h2>
<p>This code expires in 10 minutes. If you didn't request this, you can safely ignore this email.</p>