<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'meeting_id',
        'description',
    ];

    /**
     * Get the meeting that this agenda belongs to.
     */
    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }
}
