<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Room Booking Confirmation</title>
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
    </style>
</head>
<body>
    <div class="container">
        <h2>Hello {{ $organizer->name }},</h2>

        <p>
            A room has been successfully booked for your upcoming meeting.
        </p>

        <div class="section-title">📋 Meeting Details</div>
        <div class="detail"><span class="label">Title:</span> <span class="value">{{ $meeting->title }}</span></div>
        <div class="detail"><span class="label">Date:</span> <span class="value">{{ $meeting->startsAt->format('Y-m-d') }}</span></div>
        <div class="detail"><span class="label">Time:</span> <span class="value">{{ $meeting->startsAt->format('H:i') }} - {{ $meeting->endsAt->format('H:i') }}</span></div>
        <div class="detail"><span class="label">Location:</span> 
            <span class="value">IDS building, floor {{ $meeting->room->floor }}, room {{ $meeting->room->roomname }}</span>
        </div>

        <p style="margin-top: 20px;">
            Please check your dashboard for more details.
        </p>

        <p class="footer">
            Have a great day,<br>
            <strong>{{ config('app.name') }}</strong>
        </p>
    </div>
</body>
</html>
