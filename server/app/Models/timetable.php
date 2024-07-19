<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class timetable extends Model
{
    use HasFactory;

    protected $fillable = [
        'ClassID', 
        'Subject',
        'TeacherID',
        'StartingTime',
        'EndingTime',
        'Day'
    ];

    protected $table = 'timetable';


    public function class()
    {
        return $this->belongsTo(Classes::class, 'ClassID', 'id');
    }
}
