<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MeetingUpdateNotificationMail extends Mailable
{
 use Queueable, SerializesModels;

 public $meeting;
 public $attendee;

 public function __construct($meeting, $attendee)
 {
     $this->meeting = $meeting;
     $this->attendee = $attendee;
 }

 public function build()
 {
     return $this->subject('Meeting Update')
                 ->view('emails.meeting_update', [
                     'meeting' => $this->meeting,
                     'attendee' => $this->attendee,
                 ]);
 }
}
