<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class users extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */


    protected $guard = 'users';

    protected $fillable = [
        'name',
        'userName',
        'email',
        'password',
        'role'
    ];


    public function students()
    {
        return $this->hasMany(students::class, 'StudentUserID');
    }
    public function teacher()
    {
        return $this->hasMany(teachers::class, 'TeacherUserID');
    }
    public function images()
    {
        return $this->hasMany(images::class, 'UsersID');
    }


    public function subjects()
    {
        return $this->hasMany(subjects::class, 'UsersID');
    }

    /**
     * The attributes that should be hidden for serialization.
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
}
