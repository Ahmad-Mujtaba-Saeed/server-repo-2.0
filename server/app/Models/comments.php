<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class comments extends Model
{
    use HasFactory;

    protected $fillable = [
        'UsersID', 
        'Comment',
        'VideoID'
    ];

    protected $table = 'comments';

    // Define the relationship with the User model
    public function users()
    {
        return $this->belongsTo(users::class, 'UsersID');
    }
}
