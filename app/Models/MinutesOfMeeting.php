<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MinutesOfMeeting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'meeting_id',
        'decisions',
        'discussedPoints',
    ];

    /**
     * Get the meeting that this minutes record belongs to.
     */
    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function attachments(){
        return $this->hasMany(Attachment::class);
    }

    public function actionItems()
    {
        return $this->hasMany(ActionItem::class);
    }
}
