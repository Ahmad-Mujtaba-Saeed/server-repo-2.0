<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class messages extends Model
{
    use HasFactory;

    protected $fillable = [
        'Sending_id', 
        'Message',
        'Receiving_id',
        'Date',
        'time',
    ];

    protected $table = 'messages';
}
