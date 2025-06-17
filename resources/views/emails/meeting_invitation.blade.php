<p>
Hello {{ $attendee->name }},<br><br>

You have been invited to a meeting.<br><br>

Title: {{ $meeting->title }}<br>
Date: {{ $meeting->startsAt->format('Y-m-d') }}<br>
Time: {{ $meeting->startsAt->format('H:i') }} - {{ $meeting->endsAt->format('H:i') }}<br>
Location: IDS building floor {{ $meeting->room->floor }} room {{ $meeting->room->roomname }}<br>
Please check your dashboard for more details.<br><br>

</p>
Thank you,<br>
{{ config('app.name') }}

