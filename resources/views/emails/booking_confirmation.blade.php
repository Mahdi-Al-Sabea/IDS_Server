<p>
Hello {{ $organizer->name }},<br><br>

A room has been booked for your meeting.<br><br>

<b>Meeting Details:</b><br>
Title: {{ $meeting->title }}<br>
Date: {{ $meeting->startsAt->format('Y-m-d') }}<br>
Time: {{ $meeting->startsAt->format('H:i') }} - {{ $meeting->endsAt->format('H:i') }}<br>
Location: IDS building floor {{ $meeting->room->floor }} room {{ $meeting->room->roomname }}<br>
Please check your dashboard for more details.<br><br>

</p>
Have a great day,<br>
{{ config('app.name') }}

