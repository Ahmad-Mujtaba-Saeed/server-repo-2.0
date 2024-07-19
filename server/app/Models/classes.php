<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class classes extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'ClassName',
        'ClassRank',
        'ClassFloor',
        'ClassTeacherID',
        'ClassCapacity'
    ];

    protected $primaryKey = 'id';
    protected $table = 'classes'; // make sure the table name matches your database

    public function timetables()
    {
        return $this->hasMany(timetable::class, 'ClassID', 'id');
    }

    public function students()
    {
        return $this->hasMany(students::class, 'StudentClassID');
    }
    public function teachers()
    {
        return $this->belongsTo(teachers::class, 'ClassTeacherID');
    }
}
