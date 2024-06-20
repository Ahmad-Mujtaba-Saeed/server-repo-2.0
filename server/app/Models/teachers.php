<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class teachers extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'TeacherUserID',
        'TeacherDOB',
        'TeacherCNIC',
        'TeacherPhoneNumber',
        'TeacherHomeAddress',
        'TeacherReligion',
        'TeacherSalary',
        'TeacherSalaryPaid'
    ];

    protected $primaryKey = 'id';

    protected $table = 'teachers';

    
    public function students()
    {
        return $this->hasMany(students::class, 'StudentTeacherID');
    }
    public function users()
    {
        return $this->belongsTo(users::class, 'TeacherUserID');
    }
    public function classes()
    {
        return $this->hasMany(classes::class, 'ClassTeacherID');
    }
}
