<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class classes extends Model
{
    use HasFactory;

    protected $fillable = [
        'ClassName',
        'ClassRank',
        'ClassFloor',
        'ClassTeacherID',
        'ClassCapacity'
    ];

    protected $primaryKey = 'id';

    protected $table = 'classes';

    public function students()
    {
        return $this->hasMany(students::class, 'StudentClassID');
    }
    public function teachers()
    {
        return $this->belongsTo(teachers::class, 'ClassTeacherID');
    }
}
