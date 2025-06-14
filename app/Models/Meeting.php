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

}
