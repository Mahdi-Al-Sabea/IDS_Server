<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'description',
        'status',
        'dueDate',
        'assignedTo',
        'minutes_of_meeting_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'dueDate' => 'date',
    ];

    /**
     * Get the user to whom the action item is assigned.
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignedTo');
    }

    /**
     * Get the minutes of meeting associated with this action item.
     */
    public function minutesOfMeeting()
    {
        return $this->belongsTo(MinutesOfMeeting::class);
    }
}
