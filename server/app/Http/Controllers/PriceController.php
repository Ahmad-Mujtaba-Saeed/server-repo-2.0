<?php

namespace App\Http\Controllers;

use App\Models\GeneratedFee;
use App\Models\students;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class PriceController extends Controller
{
    public function GenerateStudentFee(Request $request)
    {
        $user = $request->user();
        if ($user->role == 'Admin') {
            $studentData = students::select('id', 'StudentMonthlyFee', 'StudentUserID')
            ->with('parents')
            ->get();
            $date = date('Y-m-d');
            $totalUnpaidFee = 0;
            foreach ($studentData as $student) {
                $existingFees = GeneratedFee::where('UsersID', $student['StudentUserID'])
                    ->where('Paid', false)
                    ->get();
                    $GeneratedFee =  GeneratedFee::create([
                        'UsersID' => $student['StudentUserID'],
                        'Fee' => $student['StudentMonthlyFee'],
                        'Paid' => false,
                        'Date' => $date,
                        'Role' => 'Student'
                    ]);
                    if ($existingFees) {
                        foreach ($existingFees as $fee) {
                            $totalUnpaidFee += $fee->Fee;
                        }
                        $details = [
                            'title' => 'Unpaid Student Fee',
                            'body' => 'Please Clear your Child Remaining Fee As soon as Possible to avoid any problems.',
                            'Fee' => $totalUnpaidFee +  $student['StudentMonthlyFee']
                        ];
                        Mail::to($student->parents->GuardiansEmail)->send(new \App\Mail\GeneratedFee($details));
                    }
                    else{
                        $details = [
                            'title' => 'Student New Challan Generated',
                            'body' => 'Please Pay your Children Fee  As soon as Possible to avoid any problems.',
                            'Fee' => $student['StudentMonthlyFee']
                        ];
                        Mail::to($student->parents->GuardiansEmail)->send(new \App\Mail\GeneratedFee($details));
                    }
                $totalUnpaidFee = 0;
            }
            return response()->json(['data' => $studentData]);
        }
    }
}
