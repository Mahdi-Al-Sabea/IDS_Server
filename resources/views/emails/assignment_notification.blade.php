<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Task Assigned</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 30px;
            color: #333;
        }

        .container {
            background-color: #fff;
            padding: 25px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            max-width: 600px;
            margin: auto;
        }

        h2 {
            color: #0056b3;
            margin-bottom: 20px;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-weight: 600;
            color: #444;
            margin-bottom: 8px;
        }

        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #777;
        }

        .label {
            font-weight: 500;
            color: #222;
        }

        .value {
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Hello {{ $assignee->name }},</h2>

        <p class="section">
            You have been assigned a new task related to the following meeting.
        </p>

        <div class="section">
            <div class="section-title">📅 Related Meeting</div>
            <div><span class="label">Title:</span> <span class="value">{{ $meeting->title }}</span></div>
        </div>

        <div class="section">
            <div class="section-title">📝 Task Details</div>
            <div><span class="label">Description:</span> <span class="value">{{ $actionItem->description }}</span></div>
            <div><span class="label">Due Date:</span> <span class="value">{{ \Carbon\Carbon::parse($actionItem->dueDate)->format('Y-m-d') ?? 'N/A' }}</span></div>
            <div><span class="label">Assigned By:</span> <span class="value">{{ $meeting->organizer->name }}</span></div>
        </div>

        <p class="section">
            Please check your dashboard to view and manage your task.
        </p>

        <p class="footer">
            Have a productive day,<br>
            <strong>{{ config('app.name') }}</strong>
        </p>
    </div>
</body>
</html>
