<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'heading', 
        'description',
        'teacher',
        'student'
    ];

    protected $table = 'announcement';
}
