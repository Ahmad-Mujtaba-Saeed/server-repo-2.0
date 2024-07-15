<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class expensives extends Model
{
    use HasFactory;

    protected $fillable = [
        'heading', 
        'amount',
        'description',
        'Date'
    ];

    protected $table = 'expensives';
}
