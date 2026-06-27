<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New {{ $formType === 'consultation' ? 'Consultation Booking' : 'Booking Enquiry' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background: #FAFAF9; color: #1C1917; }
        .wrapper { max-width: 560px; margin: 0 auto; padding: 40px 20px; }
        .card { background: #fff; border: 1px solid #E7E5E4; border-radius: 8px; overflow: hidden; }
        .header { background: #1C1917; padding: 32px 40px; }
        .logo { font-size: 20px; font-weight: 700; color: #fff; letter-spacing: -0.02em; }
        .logo-tag { font-size: 10px; color: #78716C; letter-spacing: 0.1em; text-transform: uppercase; margin-top: 2px; }
        .body { padding: 40px; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; margin-bottom: 20px; }
        .badge-booking { background: #EDE9FE; color: #7C3AED; }
        .badge-consultation { background: #D1FAE5; color: #059669; }
        .heading { font-size: 22px; font-weight: 600; color: #1C1917; margin-bottom: 8px; letter-spacing: -0.01em; }
        .text { font-size: 14px; color: #57534E; line-height: 1.7; margin-bottom: 24px; }
        .info-box { background: #F5F3FF; border: 1px solid #DDD6FE; border-radius: 8px; padding: 20px 24px; margin-bottom: 24px; }
        .info-label { font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #7C3AED; margin-bottom: 12px; }
        .info-row { display: flex; justify-content: space-between; padding: 7px 0; border-bottom: 1px solid #EDE9FE; font-size: 13px; }
        .info-row:last-child { border-bottom: none; }
        .info-key { color: #78716C; }
        .info-val { font-weight: 600; color: #1C1917; text-align: right; max-width: 60%; }
        .fields-box { border: 1px solid #E7E5E4; border-radius: 8px; overflow: hidden; margin-bottom: 24px; }
        .fields-header { background: #F5F5F4; padding: 10px 16px; font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #78716C; }
        .field-row { padding: 12px 16px; border-bottom: 1px solid #E7E5E4; }
        .field-row:last-child { border-bottom: none; }
        .field-label { font-size: 11px; color: #A8A29E; margin-bottom: 3px; }
        .field-value { font-size: 13px; color: #1C1917; font-weight: 500; }
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
            <div class="body">
                <div class="badge {{ $formType === 'consultation' ? 'badge-consultation' : 'badge-booking' }}">
                    {{ $formType === 'consultation' ? '📅 Consultation Booking' : '📋 Booking Enquiry' }}
                </div>
                <div class="heading">New {{ $formType === 'consultation' ? 'Consultation Booking' : 'Booking Enquiry' }}</div>
                <p class="text">
                    Hi {{ $recipientName }}, you have a new {{ $formType === 'consultation' ? 'consultation booking' : 'booking enquiry' }}
                    from <strong>{{ $submitterName }}</strong> via <strong>{{ $formName }}</strong>.
                </p>

                <div class="info-box">
                    <div class="info-label">Submission Details</div>
                    <div class="info-row">
                        <span class="info-key">From</span>
                        <span class="info-val">{{ $submitterName }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Form</span>
                        <span class="info-val">{{ $formName }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Submitted</span>
                        <span class="info-val">{{ $submittedAt }}</span>
                    </div>
                    @if($bookingDate)
                    <div class="info-row">
                        <span class="info-key">Date</span>
                        <span class="info-val">{{ $bookingDate }}</span>
                    </div>
                    @endif
                    @if($bookingTime)
                    <div class="info-row">
                        <span class="info-key">Time</span>
                        <span class="info-val">{{ $bookingTime }}</span>
                    </div>
                    @endif
                </div>

                @if(!empty($fields))
                <div class="fields-box">
                    <div class="fields-header">Form Responses</div>
                    @foreach($fields as $field)
                    <div class="field-row">
                        <div class="field-label">{{ $field['label'] }}</div>
                        <div class="field-value">{{ $field['value'] ?: '—' }}</div>
                    </div>
                    @endforeach
                </div>
                @endif

                <p style="font-size:12px;color:#A8A29E;line-height:1.7;">
                    Log in to your Koordli dashboard to view and manage this submission.
                </p>
            </div>
            <div class="footer">
                <div class="footer-text">
                    This email was sent to {{ $recipientEmail }}.<br>
                    © {{ date('Y') }} Koordli. All rights reserved.
                </div>
            </div>
        </div>
    </div>
</body>
</html>