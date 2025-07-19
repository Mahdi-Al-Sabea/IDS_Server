<p>
    Hello {{ $assignee->name }},<br><br>

    You have been assigned a new task related to a meeting.<br><br>

    <b>Related Meeting:</b><br>
    {{ $meeting->title }}<br><br>

    <b>Task Details:</b><br>
    Description: {{ $actionItem->description }}<br>
    Due Date: {{ \Carbon\Carbon::parse($actionItem->dueDate)->format('Y-m-d') ?? 'N/A' }}<br>
    <b>Assigned By:</b> {{ $meeting->organizer->name }}<br>
    Please check your dashboard to manage this task.<br><br>
</p>

<p>
    Have a productive day,<br>
    <strong>{{ config('app.name') }}</strong>
</p>

