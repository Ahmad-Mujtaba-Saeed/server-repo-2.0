<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class students extends Model
{
    use HasFactory;
    protected $fillable = [
        'StudentCNIC', 
        'StudentClassID', 
        'StudentUserID',
        'StudentGender', 
        'StudentTeacherID',
        'StudentDOB', 
        'StudentAdmissionApproval', 
        'StudentPhoneNumber', 
        'StudentHomeAddress', 
        'StudentMonthlyFee', 
        'StudentReligion'
    ];


    public function users()
    {
        return $this->belongsTo(users::class, 'StudentUserID');
    }
    public function images()
    {
        return $this->hasOne(images::class,'UsersID','StudentUserID');
    }
    public function classes()
    {
        return $this->belongsTo(classes::class, 'StudentClassID' , 'id');
    }

    // Define the relationship with the Teacher model
    public function teachers()
    {
        return $this->belongsTo(teachers::class, 'StudentTeacherID');
    }
    public function parents()
    {
        return $this->hasOne(parents::class, 'StudentID');
    }
    public function subjects()
    {
        return $this->hasMany(subjects::class, 'UsersID' , 'StudentUserID');
    }

}
