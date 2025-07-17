<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MeetingStatusChangeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $meeting;
    public $attendee;
    public $status;

    public function __construct($meeting, $attendee, $status)
    {
        $this->meeting = $meeting;
        $this->attendee = $attendee;
        $this->status = $status;
    }

    public function build()
    {
        return $this->subject('Meeting ' . $this->status)
            ->view('emails.meeting_status_change', [
                'meeting' => $this->meeting,
                'attendee' => $this->attendee,
                'status' => $this->status,
            ]);
    }
}
