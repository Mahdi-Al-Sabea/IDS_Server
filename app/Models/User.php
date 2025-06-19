<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Or use HasApiTokens from Passport if needed

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'profile_picture',
        'role',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays and JSON.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'uploadedBy');
    }

    public function actionItems()
    {
        return $this->hasMany(ActionItem::class, 'assignedTo');
    }

    public function notifications(){
        return $this->hasMany(Notification::class,'receiver_id');
    }

    public function meetings(){
        return $this->belongsToMany(User::class, 'meeting_attendee', 'user_id', 'meeting_id');

    }


}
