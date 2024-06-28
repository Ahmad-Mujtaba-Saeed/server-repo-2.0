<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class videoupload extends Model
{
    use HasFactory;

    protected $fillable = [
        'UsersID',
        'VideoName', 
        'VideoCategory',
        'VideoTitle',
        'VideoDescription',
        'VideoRank',
        'VideoPlaylistID',
        'UploadedImgID',
        'Date'
    ];

    protected $table = 'VideoUpload';

    // Define the relationship with the User model
    public function users()
    {
        return $this->belongsTo(users::class, 'UsersID');
    }
    public function playlists()
    {
        return $this->belongsTo(PlaylistVideo::class, 'VideoPlaylistID');
    }
    public function images()
    {
        return $this->belongsTo(images::class, 'UploadedImgID');
    }
}
