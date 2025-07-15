<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Minutes of Meeting - {{ $minutes->meeting->title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }
        .meeting-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 30px;
        }
        .header-border {
            border-bottom: 2px solid #ccc;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .attendee-card {
            background-color: #f0f0f0;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .agenda-item {
            margin-bottom: 8px;
        }
        .section-title {
            font-size: 18px;
            margin-top: 30px;
            margin-bottom: 15px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #aaa;
            padding: 8px;
            text-align: left;
        }
        .status-completed {
            color: green;
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="meeting-container">
        <!-- Header -->
        <div class="header-border">
            <h1>Minutes of Meeting</h1>
            <h2>{{ $minutes->meeting->title }}</h2>
            <p><strong>Date:</strong> {{ $minutes->meeting->startsAt->format('D, M j, Y') }}</p>
            <p><strong>Time:</strong> {{ $minutes->meeting->startsAt->format('h:i A') }} - {{ $minutes->meeting->endsAt->format('h:i A') }}</p>
            <p><strong>Location:</strong> {{ $minutes->meeting->room->roomname }} (Floor: {{ $minutes->meeting->room->floor }}) (Capacity: {{ $minutes->meeting->room->capacity }})</p>
            <p><strong>Organizer:</strong> {{ $minutes->meeting->organizer->name }}</p>
            <p><strong>Status:</strong> <span class="status-completed">{{ $minutes->meeting->status }}</span></p>
        </div>

        <!-- Attendees -->
        <div>
            <div class="section-title">Attendees</div>
            @foreach(['Admin', 'Employee', 'Guest'] as $role)
                <div class="attendee-card">
                    <h4>{{ $role }}</h4>
                    <ul>
                        @foreach($minutes->meeting->attendees->where('role', $role) as $attendee)
                            <li>{{ $attendee->name }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <!-- Agenda -->
        <div>
            <div class="section-title">Agenda</div>
            <ul>
                @foreach($minutes->meeting->agendas as $agenda)
                    <li class="agenda-item"> {{ $agenda->description }}</li>
                @endforeach
            </ul>
        </div>

        <!-- Discussions & Decisions -->
        <div class="section-title">Discussions & Decisions</div>
        <div>
            <strong>Discussed Topics:</strong>
          <p>{{ $minutes->discussedPoints }}</p>

            <strong>Decisions:</strong>
            <p>{{ $minutes->decisions }}</p>
        </div>

        <!-- Action Items -->
        <div>
            <div class="section-title">Action Items</div>
            <table>
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Assigned To</th>
                        <th>Due Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($minutes->actionItems as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td>{{ $item->assignedTo }}</td>
                            <td>{{ $item->dueDate->format('m/d/Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Attachments -->
<!--         @if($minutes->attachments->count() > 0)
            <div>
                <div class="section-title">Attachments</div>
                <ul>
                    @foreach($minutes->attachments as $file)
                        <li>{{ asset($file->filePath) }}</li>
                    @endforeach
                </ul>
            </div>
        @endif -->
    </div>
</body>
</html>
