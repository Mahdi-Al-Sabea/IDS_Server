<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Meeting Invitation</title>
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

        .section-title {
            font-weight: 600;
            margin-top: 20px;
            color: #444;
        }

        .detail {
            margin: 5px 0;
        }

        .label {
            font-weight: 500;
            color: #222;
        }

        .value {
            color: #555;
        }

        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #777;
        }

        .credentials {
            background-color: #f0f2f5;
            padding: 10px 15px;
            border-radius: 6px;
            margin-top: 15px;
        }

    </style>
</head>
<body>
    <div class="container">
        <h2>Hello {{ $attendee->name }},</h2>

        <p>
            You have been invited to a meeting at <strong>IDS Company</strong>.
        </p>

        @if ($attendee->role === 'Guest')
            <div class="section-title">🔐 Guest Login Credentials</div>
            <div class="credentials">
                <div><span class="label">Email:</span> <span class="value">{{ $attendee->email }}</span></div>
                <div><span class="label">Password:</span> <span class="value">123456</span></div>
            </div>
        @endif

        <div class="section-title">📋 Meeting Details</div>
        <div class="detail"><span class="label">Title:</span> <span class="value">{{ $meeting->title }}</span></div>
        <div class="detail"><span class="label">Date:</span> <span class="value">{{ $meeting->startsAt->format('Y-m-d') }}</span></div>
        <div class="detail"><span class="label">Time:</span> 
            <span class="value">{{ $meeting->startsAt->format('H:i') }} - {{ $meeting->endsAt->format('H:i') }}</span>
        </div>
        <div class="detail"><span class="label">Location:</span> 
            <span class="value">IDS building, floor {{ $meeting->room->floor }}, room {{ $meeting->room->roomname }}</span>
        </div>

        <p style="margin-top: 20px;">
            Please check your dashboard for more details and to confirm your attendance.
        </p>

        <p class="footer">
            Thank you,<br>
            <strong>{{ config('app.name') }}</strong>
        </p>
    </div>
</body>
</html>
