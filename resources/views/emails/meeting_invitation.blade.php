<p>
Hello {{ $attendee->name }},<br><br>

You have been invited to a meeting in IDS Company . 
<br>
@if ($attendee->role == 'Guest')
Credentials for your account : <br>
Email: {{ $attendee->email }}<br>
Password: 123456
<br>
<br>
@else
<br>
@endif
Title: {{ $meeting->title }}<br>
Date: {{ $meeting->startsAt->format('Y-m-d') }}<br>
Time: {{ $meeting->startsAt->format('H:i') }} - {{ $meeting->endsAt->format('H:i') }}<br>
Location: IDS building floor {{ $meeting->room->floor }} room {{ $meeting->room->roomname }}<br>
Please check your dashboard for more details.<br><br>

</p>
Thank you,<br>
{{ config('app.name') }}

