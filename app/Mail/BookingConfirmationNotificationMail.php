<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmationNotificationMail extends Mailable
{
 use Queueable, SerializesModels;

 public $meeting;
 public $organizer;

 public function __construct($meeting, $organizer)
 {
     $this->meeting = $meeting;
     $this->organizer = $organizer;
 }

 public function build()
 {
     return $this->subject('Booking Confirmation for Meeting')
                 ->view('emails.booking_confirmation', [
                     'meeting' => $this->meeting,
                     'organizer' => $this->organizer,
                 ]);
 }
}
