<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'status',
        'startsAt',
        'endsAt',
        'room_id',
         'organizer_id', 
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'startsAt' => 'datetime',
        'endsAt' => 'datetime',
    ];

    public function agendas(){
        return $this->hasMany(Agenda::class);
    }

    public function minutes()
    {
        return $this->hasOne(MinutesOfMeeting::class);
    }

    public function attendees(){
        return $this->belongsToMany(User::class, 'meeting_attendee', 'meeting_id', 'user_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }
}
