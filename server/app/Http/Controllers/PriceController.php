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
                            'Subject' => 'Please Pay Unpaid Amount',
                            'title' => 'Unpaid Student Fee',
                            'body' => 'Please Clear your Child Remaining Fee As soon as Possible to avoid any problems.',
                            'Fee' => $totalUnpaidFee +  $student['StudentMonthlyFee']
                        ];
                        Mail::to($student->parents->GuardiansEmail)->send(new \App\Mail\GeneratedFee($details));
                    }
                    else{
                        $details = [
                            'Subject' => 'New Challan Generated',
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
    public function GenerateStudentFeePaid(Request $request){
        $ID = $request->input('ID');
        $Date = $request->input('Date');
        $user = $request->user();
    
        if ($user->role != 'Admin') {
            return response()->json(['success' => false, 'message' => 'Only admin can access this route']);
        }
    
        $GeneratedFee = GeneratedFee::where('Date', $Date)->where('UsersID', $ID)->first();
    
        if ($GeneratedFee) {
            $GeneratedFee->Paid = true;
            $GeneratedFee->save();
    
            $student = students::select('id')
                ->with('parents')
                ->where('StudentUserID', $ID)
                ->first();
    
            if ($student && $student->parents) {
                $details = [
                    'Subject' => 'Fee Successfully Paid',
                    'title' => 'Fee Successfully Paid',
                    'body' => "You have paid your children's fee for this date: {$GeneratedFee->Date}",
                    'Fee' => $GeneratedFee->Fee
                ];
    
                Mail::to($student->parents->GuardiansEmail)->send(new \App\Mail\GeneratedFee($details));
            }
    
            return response()->json(['success' => true, 'message' => 'Fee Successfully Paid']);
        } else {
            return response()->json(['success' => false, 'message' => 'Generated fee record not found']);
        }
    }
    
}
