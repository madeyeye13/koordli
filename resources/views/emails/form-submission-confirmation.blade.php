<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $formType === 'consultation' ? 'Consultation Confirmed' : 'Booking Received' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background: #FAFAF9; color: #1C1917; }
        .wrapper { max-width: 560px; margin: 0 auto; padding: 40px 20px; }
        .card { background: #fff; border: 1px solid #E7E5E4; border-radius: 8px; overflow: hidden; }
        .header { background: #1C1917; padding: 32px 40px; }
        .logo { font-size: 20px; font-weight: 700; color: #fff; letter-spacing: -0.02em; }
        .logo-tag { font-size: 10px; color: #78716C; letter-spacing: 0.1em; text-transform: uppercase; margin-top: 2px; }
        .banner { padding: 20px 40px; background: #F0FDF4; border-bottom: 1px solid #86EFAC; display: flex; align-items: center; gap: 12px; }
        .banner-icon { font-size: 24px; }
        .banner-text { font-size: 15px; font-weight: 600; color: #166534; }
        .body { padding: 40px; }
        .heading { font-size: 22px; font-weight: 600; color: #1C1917; margin-bottom: 8px; }
        .text { font-size: 14px; color: #57534E; line-height: 1.7; margin-bottom: 24px; }
        .detail-box { background: #F5F5F4; border-radius: 8px; padding: 20px 24px; margin-bottom: 24px; }
        .detail-label { font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #A8A29E; margin-bottom: 12px; }
        .detail-row { display: flex; justify-content: space-between; padding: 7px 0; border-bottom: 1px solid #E7E5E4; font-size: 13px; }
        .detail-row:last-child { border-bottom: none; }
        .detail-key { color: #78716C; }
        .detail-val { font-weight: 600; color: #1C1917; }
        .contact-box { border: 1px solid #E7E5E4; border-radius: 8px; padding: 20px 24px; margin-bottom: 24px; }
        .contact-label { font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #A8A29E; margin-bottom: 12px; }
        .contact-name { font-size: 16px; font-weight: 600; color: #1C1917; margin-bottom: 6px; }
        .contact-item { font-size: 13px; color: #57534E; margin-bottom: 4px; }
        .footer { padding: 20px 40px; background: #FAFAF9; border-top: 1px solid #E7E5E4; }
        .footer-text { font-size: 11px; color: #A8A29E; line-height: 1.7; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <div class="logo">Koordli</div>
                <div class="logo-tag">Event Operations Platform</div>
            </div>

            <div class="banner">
                <span class="banner-icon">{{ $formType === 'consultation' ? '📅' : '✅' }}</span>
                <div class="banner-text">
                    {{ $formType === 'consultation' ? 'Consultation Booking Confirmed!' : 'Booking Enquiry Received!' }}
                </div>
            </div>

            <div class="body">
                <div class="heading">Hi {{ $guestName }},</div>
                <p class="text">
                    @if($formType === 'consultation')
                    Your consultation has been booked successfully. Here are your booking details.
                    @else
                    Thank you for your booking enquiry. We've received your information and will be in touch shortly.
                    @endif
                </p>

                @if($bookingDate || $bookingTime)
                <div class="detail-box">
                    <div class="detail-label">
                        {{ $formType === 'consultation' ? 'Consultation Details' : 'Booking Details' }}
                    </div>
                    @if($bookingDate)
                    <div class="detail-row">
                        <span class="detail-key">Date</span>
                        <span class="detail-val">{{ $bookingDate }}</span>
                    </div>
                    @endif
                    @if($bookingTime)
                    <div class="detail-row">
                        <span class="detail-key">Time</span>
                        <span class="detail-val">{{ $bookingTime }}</span>
                    </div>
                    @endif
                    @if($consultationType)
                    <div class="detail-row">
                        <span class="detail-key">Type</span>
                        <span class="detail-val">{{ ucfirst($consultationType) }}</span>
                    </div>
                    @endif
                    @if($consultationType === 'physical' && $location)
                    <div class="detail-row">
                        <span class="detail-key">Location</span>
                        <span class="detail-val">{{ $location }}</span>
                    </div>
                    @endif
                    @if($consultationType === 'virtual' && $meetingLink)
                    <div class="detail-row">
                        <span class="detail-key">Meeting Link</span>
                        <span class="detail-val"><a href="{{ $meetingLink }}" style="color:#7C3AED;">Join Meeting</a></span>
                    </div>
                    @endif
                </div>
                @endif

                <div class="contact-box">
                    <div class="contact-label">Contact Information</div>
                    <div class="contact-name">{{ $tenantName }}</div>
                    @if($tenantEmail)
                    <div class="contact-item">✉️ {{ $tenantEmail }}</div>
                    @endif
                    @if($tenantPhone)
                    <div class="contact-item">📞 {{ $tenantPhone }}</div>
                    @endif
                </div>

                <p style="font-size:12px;color:#A8A29E;line-height:1.7;">
                    If you have any questions, please reach out to {{ $tenantName }} directly using the contact information above.
                </p>
            </div>
            <div class="footer">
                <div class="footer-text">
                    This email was sent to {{ $guestEmail }}.<br>
                    © {{ date('Y') }} Koordli. All rights reserved.
                </div>
            </div>
        </div>
    </div>
</body>
</html>