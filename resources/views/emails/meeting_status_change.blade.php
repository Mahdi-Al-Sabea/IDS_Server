<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Meeting Status Update</title>
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

        .highlight {
            font-weight: 600;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Hello {{ $attendee->name }},</h2>

        <p>
            The status of your meeting titled <span class="highlight">"{{ $meeting->title }}"</span> has changed.
        </p>

        <div class="status-box">
            Meeting Status: <strong>{{ ucfirst($status) }}</strong>
        </div>

        <p style="margin-top: 20px;">
            Please check your dashboard for more details.
        </p>

        <p class="footer">
            Thank you,<br>
            <strong>{{ config('app.name') }}</strong>
        </p>
    </div>
</body>
</html>
