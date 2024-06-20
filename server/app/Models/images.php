<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class images extends Model
{
    use HasFactory;


    protected $primaryKey = 'id';


    protected $fillable = [
        'UsersID',
        'ImageName'
    ];

    public function images()
    {
        return $this->belongsTo(users::class, 'UsersID');
    }
}
