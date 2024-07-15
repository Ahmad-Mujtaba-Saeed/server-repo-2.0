<?php

namespace App\Http\Controllers;

use App\Models\expensives;
use App\Models\GeneratedFee;
use App\Models\students;
use App\Models\teachers;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Validator;

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
                $GeneratedFee = GeneratedFee::create([
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
                        'Fee' => $totalUnpaidFee + $student['StudentMonthlyFee']
                    ];
                    Mail::to($student->parents->GuardiansEmail)->send(new \App\Mail\GeneratedFee($details));
                } else {
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
            return response()->json(['data' => $studentData, 'message' => 'Successfully Generated Fee Challan']);
        }
    }
    public function GenerateStudentFeePaid(Request $request)
    {
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
            $GeneratedFee = GeneratedFee::with('users:id,email,name')->where('UsersID', $ID)->get();
            $totalPaidAmount = 0;
            $totalUnPaidAmount = 0;
            
            foreach ($GeneratedFee as $fee) {
                if ($fee->Paid) {
                    $totalPaidAmount += $fee->Fee;
                }
                else{
                    $totalUnPaidAmount += $fee->Fee;
                }
            }
            return response()->json(['success' => true, 'data' => $GeneratedFee, 'message' => 'Fee Successfully Paid', 'totalPaidAmount' => $totalPaidAmount ,'totalUnPaidAmount' => $totalUnPaidAmount]);
        } else {
            return response()->json(['success' => false, 'message' => 'Generated fee record not found']);
        }
    }
    public function TeacherPayPaid(Request $request)
    {
        $user = $request->user();
        if ($user->role != 'Admin') {
            return response()->json(['success' => false, 'message' => 'Only admin can access this route']);
        }
        if($request->query('ID')){
        $ID = $request->query('ID');
        $date = date('Y-m-d');
        $teacher = teachers::with('users')->where('TeacherUserID', $ID)->first();
        $GeneratedFee = GeneratedFee::create([
            'UsersID' => $teacher['TeacherUserID'],
            'Fee' => $teacher['TeacherSalary'],
            'Paid' => true,
            'Date' => $date,
            'Role' => 'Teacher'
        ]);
        if ($GeneratedFee) {
            $details = [
                'Subject' => 'Teacher Pay Paid Successfully',
                'title' => 'Teacher Pay Paid Successfully',
                'body' => "Teacher has been paid for this month : {$GeneratedFee->Date}",
                'Fee' => $GeneratedFee->Fee
            ];
            Mail::to($teacher->users->email)->send(new \App\Mail\GeneratedFee($details));
            return response()->json(['success' => true, 'message' => 'Pay Paid Successfully']);
        } else {
            return response()->json(['success' => true, 'message' => 'Failed to Pay Paid']);
        }
    }else{
        $date = date('Y-m-d');
        $teachers = teachers::with('users')->get();
        foreach($teachers as $teacher){
        $GeneratedFee = GeneratedFee::create([
            'UsersID' => $teacher['TeacherUserID'],
            'Fee' => $teacher['TeacherSalary'],
            'Paid' => true,
            'Date' => $date,
            'Role' => 'Teacher'
        ]);
        if ($GeneratedFee) {
            $details = [
                'Subject' => 'Teacher Pay Paid Successfully',
                'title' => 'Teacher Pay Paid Successfully',
                'body' => "Teacher has been paid for this month : {$GeneratedFee->Date}",
                'Fee' => $GeneratedFee->Fee
            ];
            Mail::to($teacher->users->email)->send(new \App\Mail\GeneratedFee($details));
        }
        }
        return response()->json(['success' => true, 'message' => 'All Teachers Pay Paid Successfully']);
    }
    }

    public function GeneratedPaidFee(Request $request)
    {
        $user = $request->user();
        if ($user->role != 'Admin') {
            return response()->json(['success' => true, 'message' => 'Only admin can see fee information']);
        }
        $currentYear = Carbon::now()->year;
        $GeneratedFee =  GeneratedFee::where('Paid', true)
        ->where('Role', 'Student')
        ->whereYear(DB::raw('"Date"'), $currentYear)
        ->selectRaw('TO_CHAR("Date", \'Month\') as month_name, EXTRACT(MONTH FROM "Date") as month_number, SUM("Fee"::numeric) as total_fee')
        ->groupBy('month_name', 'month_number')
        ->orderBy('month_number')
        ->get();
        $YearlyTotalFee = 0;
        foreach($GeneratedFee as $Fee){
            $YearlyTotalFee += $Fee->total_fee ;
        }
        if ($GeneratedFee) {
            return response()->json(['success' => true, 'data' => $GeneratedFee ,'YearlyTotalFee' => number_format($YearlyTotalFee)]);
        } else {
            return response()->json(['success' => true, 'message' => 'Error Fetching Fee information']);
        }
    }

    public function StudentGeneratedFee(Request $request)
    {
        $user = $request->user();
        if ($user->role != 'Admin') {
            return response()->json(['success' => true, 'message' => 'Only admin can see fee information']);
        }
        $ID = $request->query('ID');
        $GeneratedFee = GeneratedFee::with('users:id,email,name')->where('UsersID', $ID)->get();
        $totalPaidAmount = 0;
        $totalUnPaidAmount = 0;

        foreach ($GeneratedFee as $fee) {
            if ($fee->Paid) {
                $totalPaidAmount += $fee->Fee;
            }
            else{
                $totalUnPaidAmount += $fee->Fee;
            }
        }
        if ($GeneratedFee) {
            return response()->json(['success' => true, 'data' => $GeneratedFee , 'totalPaidAmount' => number_format($totalPaidAmount) ,'totalUnPaidAmount' => number_format($totalUnPaidAmount)]);
        } else {
            return response()->json(['success' => true, 'message' => 'Error Fetching Fee information']);
        }
    }
    public function Addexpensives(Request $request){
        $validator = Validator::make($request->all(),[
            'heading' => 'required|max:400',
            'amount' => 'required|integer',
            'description' => 'required|max:1000'
        ]);
        if($validator->fails()){
            return response()->json(['success' => false, 'message' => $validator->errors()]);
        }
        $user = $request->user();
        if($user->role != 'Admin'){
            return response()->json(['success' => false, 'message' => 'Only admin can add expensive']);
        }
        $expensives = expensives::create([
            'heading' => $request->input('heading'),
            'amount' => $request->input('amount'),
            'description' => $request->input('description'),
            'Date' => date('Y-m-d')
        ]);
        if($expensives){
            return response()->json(['success' => true, 'message' => 'Expensive added successfully']);
        }
        else{
            return response()->json(['success' => false, 'message' => 'Failed to add expensives']);
        }
    }
    public function TotalExpensives(Request $request)
    {
        $user = $request->user();
        if($user->role != 'Admin'){
            return response()->json(['success' => false, 'message' => 'Only admin can add expensive']);
        }
        $currentYear = Carbon::now()->year;
        $TeacherPay = GeneratedFee::where('Paid', true)
        ->where('Role', 'Teacher')
        ->whereYear(DB::raw('"Date"'), $currentYear)
        ->selectRaw('TO_CHAR("Date", \'Month\') as month_name, EXTRACT(MONTH FROM "Date") as month_number, SUM("Fee"::numeric) as total_fee')
        ->groupBy('month_name', 'month_number')
        ->orderBy('month_number')
        ->get();
    
    // Fetch expenses
    $expensives = expensives::whereYear(DB::raw('"Date"'), $currentYear)
        ->selectRaw('TO_CHAR("Date", \'Month\') as month_name, EXTRACT(MONTH FROM "Date") as month_number, SUM("amount"::numeric) as total_amount')
        ->groupBy('month_name', 'month_number')
        ->orderBy('month_number')
        ->get();
    
    // Initialize an array to hold combined results
    $combinedResults = [];

    // Merge TeacherPay results
    foreach ($TeacherPay as $teacher) {
        $monthNumber = $teacher->month_number;
        $monthName = $teacher->month_name;
        $totalFee = $teacher->total_fee;
    
        if (!isset($combinedResults[$monthNumber])) {
            $combinedResults[$monthNumber] = [
                'month_name' => $monthName,
                'month_number' => $monthNumber,
                'total_fee' => $totalFee,
                'total_amount' => 0,
            ];
        } else {
            $combinedResults[$monthNumber]['total_fee'] += $totalFee;
        }
    }
    
    // Merge expenses results
    foreach ($expensives as $expense) {
        $monthNumber = $expense->month_number;
        $totalAmount = $expense->total_amount;
    
        if (isset($combinedResults[$monthNumber])) {
            $combinedResults[$monthNumber]['total_amount'] += $totalAmount;
        } else {
            $combinedResults[$monthNumber] = [
                'month_name' => $expense->month_name,
                'month_number' => $monthNumber,
                'total_fee' => 0,
                'total_amount' => $totalAmount,
            ];
        }
    }
    
    // Calculate total_expensive combining total_fee and total_amount
    foreach ($combinedResults as &$result) {
        $result['total_expensive'] = $result['total_fee'] + $result['total_amount'];
    }
    
    // Reset the reference to avoid accidental modification elsewhere
    unset($result);
    
    // Now $combinedResults contains the combined data with total_expensive added
    // Pass $combinedResults to your view for display
    
    
    // Convert combinedResults to a simple array for easier manipulation in the frontend
    $combinedResults = array_values($combinedResults);

    $TotalExpensive = 0;

    foreach ($combinedResults as $Fee) {
        $TotalExpensive += $Fee['total_expensive'];
    }
    
        if ($TeacherPay) {
            return response()->json(['success' => true , 'TotalExpensive' => number_format($TotalExpensive) ,'combinedResults' => $combinedResults]);
        } else {
            return response()->json(['success' => true, 'message' => 'Error Fetching Fee information']);
        }
    }
}
