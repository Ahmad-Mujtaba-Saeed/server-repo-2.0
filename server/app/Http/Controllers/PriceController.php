<?php

namespace App\Http\Controllers;

use App\Models\GeneratedFee;
use App\Models\students;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PriceController extends Controller
{
    public function GenerateStudentFee(Request $request)
    {
        $user = $request->user();
        if ($user->role == 'Admin') {
            $studentData = students::select('id', 'StudentMonthlyFee', 'StudentUserID')->get();

            $date = date('Y-m-d');
            $totalUnpaidFee = 0;
            foreach ($studentData as $student) {
                $existingFees = GeneratedFee::where('UsersID', $student['StudentUserID'])
                    ->where('Paid', false)
                    ->get();
                
                    if ($existingFees) {
                        foreach ($existingFees as $fee) {
                            $totalUnpaidFee += $fee->Fee;
                        }
                    }
                GeneratedFee::create([
                    'UsersID' => $student['StudentUserID'],
                    'Fee' => $student['StudentMonthlyFee'],
                    'Paid' => false,
                    'Date' => $date,
                    'Role' => 'Student'
                ]);
                
                $totalUnpaidFee = 0;
            }
            return response()->json(['data' => $studentData]);
        }
    }
}
