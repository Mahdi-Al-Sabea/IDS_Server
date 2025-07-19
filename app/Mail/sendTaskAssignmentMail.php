<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class sendTaskAssignmentMail extends Mailable
{
    use Queueable, SerializesModels;

    public $actionItem;
    public $assignee;
    public $meeting;

    public function __construct($meeting, $actionItem, $assignee)
    {
        $this->actionItem = $actionItem;
        $this->assignee = $assignee;
        $this->meeting = $meeting;
    }
    
    public function build()
    {
        return $this->subject('New Assignment')
            ->view('emails.assignment_notification', [
                'actionItem' => $this->actionItem,
                'assignee' => $this->assignee,
                'meeting' => $this->meeting,
            ]);
    }
}
