<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fileName',
        'filePath',
        'uploadedBy',
        'minutes_of_meeting_id',
    ];

    /**
     * Get the user who uploaded the attachment.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploadedBy');
    }

    /**
     * Get the minutes of meeting this attachment belongs to.
     */
    public function minutesOfMeeting()
    {
        return $this->belongsTo(MinutesOfMeeting::class);
    }
}
