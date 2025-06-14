<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'receiver_id',
        'subject',
        'content',
    ];

    /**
     * Get the user who received the notification.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
