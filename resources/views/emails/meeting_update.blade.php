Hello {{ $attendee->name }},<br><br>

@if ($meeting->status === 'cancelled')
    You have been notified that a meeting you are invited at {{ $meeting->startsAt }} has been cancelled.<br><br>
@else
    You have been notified that the meeting "{{ $meeting->title }}" you were invited to has been rescheduled.<br><br>
@endif

Thank you,<br>
{{ config('app.name') }}