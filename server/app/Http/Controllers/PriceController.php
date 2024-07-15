<?php

namespace App\Http\Controllers;

use App\Models\GeneratedFee;
use App\Models\students;
use App\Models\teachers;
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
            return response()->json(['data' => $studentData , 'message' => 'Successfully Generated Fee Challan']);
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
    public function TeacherPayPaid(Request $request){
        $ID = $request->query('ID');
        $user = $request->user();
    
        if ($user->role != 'Admin') {
            return response()->json(['success' => false, 'message' => 'Only admin can access this route']);
        }
        $date = date('Y-m-d');
        $teacher = teachers::with('users')->where('TeacherUserID', $ID)->first();
        $GeneratedFee =  GeneratedFee::create([
            'UsersID' => $teacher['TeacherUserID'],
            'Fee' => $teacher['TeacherSalary'],
            'Paid' => true,
            'Date' => $date,
            'Role' => 'Teacher'
        ]);
        if($GeneratedFee){
            $details = [
                'Subject' => 'Teacher Pay Paid Successfully',
                'title' => 'Teacher Pay Paid Successfully',
                'body' => "Teacher has been paid for this month : {$GeneratedFee->Date}",
                'Fee' => $GeneratedFee->Fee
            ];
            Mail::to($teacher->users->email)->send(new \App\Mail\GeneratedFee($details));
            return response()->json(['success' => true, 'message' => 'Pay Paid Successfully']);
        }else{
            return response()->json(['success' => true, 'message' => 'Failed to Pay Paid']);
        }

    }

    public function GeneratedFee(Request $request){
        $user = $request->user();
        if($user->role != 'Admin'){
            return response()->json(['success' => true, 'message' => 'Only admin can see fee information']);
        }
        $GeneratedFee = GeneratedFee::all();
        if($GeneratedFee){
            return response()->json(['success' => true, 'data' => $GeneratedFee]);
        }
        else{
            return response()->json(['success' => true, 'message' => 'Error Fetching Fee information']);
        }
    }

    public function StudentGeneratedFee(Request $request){
        $user = $request->user();
        if($user->role != 'Admin'){
            return response()->json(['success' => true, 'message' => 'Only admin can see fee information']);
        }
        $ID = $request->query('ID');
        $GeneratedFee = GeneratedFee::with('users:id,email,name')->where('UsersID', $ID)->get();
        if($GeneratedFee){
            return response()->json(['success' => true, 'data' => $GeneratedFee ]);
        }
        else{
            return response()->json(['success' => true, 'message' => 'Error Fetching Fee information']);
        }
    }
}
