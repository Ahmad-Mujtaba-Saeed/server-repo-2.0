<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class parents extends Model
{
    use HasFactory;


    protected $table = 'parents';

    protected $fillable = [
        'StudentID', 
        'FatherName', 
        'MotherName', 
        'GuardiansCNIC', 
        'GuardiansPhoneNumber', 
        'GuardiansPhoneNumber2', 
        'GuardiansEmail', 
        'HomeAddress'
    ];

    // Define the relationship with the Student model
    public function student()
    {
        return $this->belongsTo(students::class, 'StudentID');
    }
}
