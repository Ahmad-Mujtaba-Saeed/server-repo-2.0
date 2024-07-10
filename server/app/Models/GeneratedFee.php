<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneratedFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'UsersID', 
        'Fee',
        'Paid',
        'Date',
        'Role'
    ];

    protected $table = 'generatedfee';

    // Define the relationship with the User model
    public function users()
    {
        return $this->belongsTo(users::class, 'UsersID');
    }
}
