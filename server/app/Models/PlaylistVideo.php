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
        'playlistCategory',
        'Date'
    ];

    protected $table = 'VideoPlaylist';

    // Define the relationship with the User model
    public function users()
    {
        return $this->belongsTo(users::class, 'UsersID');
    }
}
