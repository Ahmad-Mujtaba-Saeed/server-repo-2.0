<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaylistVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'UsersID', 
        'PlaylistTitle',
        'PlaylistDescription',
        'PlaylistRank',
        'PlaylistCategory',
        'Date'
    ];

    protected $table = 'videoplaylist';

    // Define the relationship with the User model
    public function users()
    {
        return $this->belongsTo(users::class, 'UsersID');
    }
    public function videos()
    {
        return $this->hasMany(videoupload::class, 'VideoPlaylistID');
    }
}
