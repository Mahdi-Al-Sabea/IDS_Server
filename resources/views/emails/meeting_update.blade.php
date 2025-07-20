<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Meeting Update Notification</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 30px;
            color: #333;
        }

        .container {
            background-color: #ffffff;
            padding: 25px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            max-width: 600px;
            margin: auto;
        }

        h2 {
            color: #0d6efd;
            margin-bottom: 20px;
        }

        .highlight {
            font-weight: 600;
            color: #0d6efd;
        }

        .status-box {
            background-color: #f0f2f5;
            padding: 15px;
            border-left: 4px solid #0d6efd;
            margin-top: 15px;
            border-radius: 5px;
        }

        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Hello {{ $attendee->name }},</h2>

        @if ($meeting->status === 'cancelled')
            <p>
                You have been notified that the meeting you were invited to on 
                <span class="highlight">{{ \Carbon\Carbon::parse($meeting->startsAt)->format('Y-m-d H:i') }}</span> has been <strong>cancelled</strong>.
            </p>
        @else
            <p>
                You have been notified that the meeting 
                <span class="highlight">"{{ $meeting->title }}"</span> you were invited to has been <strong>rescheduled</strong>.
                <div class="detail"><span class="label">Date:</span> <span class="value">{{ $meeting->startsAt->format('Y-m-d') }}</span></div>
                <div class="detail"><span class="label">Time:</span> 
                    <span class="value">{{ $meeting->startsAt->format('H:i') }} - {{ $meeting->endsAt->format('H:i') }}</span>
                </div>
            </p>
        @endif

        <p style="margin-top: 20px;">
            Please check your dashboard for updated information.
        </p>

        <p class="footer">
            Thank you,<br>
            <strong>{{ config('app.name') }}</strong>
        </p>
    </div>
</body>
</html>
