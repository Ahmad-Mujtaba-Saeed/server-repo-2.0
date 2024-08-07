<?php

namespace App\Http\Controllers;

use App\Models\attendance;
use App\Models\classes;
use App\Models\images;
use App\Models\parents;
use App\Models\students;
use App\Models\subjects;
use Illuminate\Http\Request;
use Validator;
use Auth;
use App\Models\users;
use App\Models\teachers;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;


class student extends Controller
{
    public function CreateStudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'userName' => 'required|unique:users|string|max:255',
            'email' => 'required|email|unique:users',
            'StudentDOB' => 'required|date',
            'StudentGender' => 'required|string',
            'StudentCNIC' => 'required|string|max:255',
            'subjects' => 'required',
            'StudentClassID' => 'required|string',
            'StudentPhoneNumber' => 'required|string|max:125',
            'StudentHomeAddress' => 'required|string|max:255',
            'StudentReligion' => 'required|string|max:255',
            'StudentMonthlyFee' => 'required',
            'FatherName' => 'required|string|max:255',
            'MotherName' => 'required|string|max:255',
            'GuardiansCNIC' => 'required|string|max:255',
            'GuardiansPhoneNumber' => 'required|string|max:255',
            'GuardiansPhoneNumber2' => 'string|max:255',
            'HomeAddress' => 'required|string|max:255',
            'GuardiansEmail' => 'required|email|max:255',
            'image' => 'required'
        ]);
        
        if ($validator->fails()) {
            // Log the errors to debug
            \Log::error($validator->errors());
            return ReturnData(false,'',$validator->errors());
        }

        $user = $request->user();

        if ($user->role !== "Admin") {
            return ReturnData(false,'','Only admin can access this');
        }

        DB::beginTransaction();

        try {
            $email = $request->input('email');
            $ClassID = $request->input('StudentClassID');
            $password = Str::random(12);
            $BcryptPassword = bcrypt($password);
            $role = "Student";

            // Create user
            $user = users::create([
                'name' => $request->input('name'),
                'userName' => $request->input('userName'),
                'role' => $role,
                'email' => $request->input('email'),
                'password' => $BcryptPassword
            ]);

            $userId = $user->id;
            $subjects = $request->input('subjects');

            // Create subjects
            foreach ($subjects as $subject) {
                subjects::create([
                    'UsersID' => $userId,
                    'SubjectName' => $subject
                ]);
            }

            // Handle image upload
            $pic = $request->input('image');
            if (isset($pic)) {
                $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $pic));
                if ($imageData === false) {
                    throw new \Exception('Failed to decode image data');
                }

                $extension = image_type_to_extension(exif_imagetype($pic));
                $filename = uniqid() . $extension;
                $storagePath = 'images/';
                $savePath = public_path($storagePath . $filename);

                if (file_put_contents($savePath, $imageData) === false) {
                    throw new \Exception('Failed to save image file');
                }

                $image = new images();
                $image->UsersID = $userId;
                $image->ImageName = $storagePath . $filename;
                $image->save();
            }

            // Get class and teacher ID
            $class = classes::find($ClassID);
            if (!$class) {
                throw new \Exception('Class not found');
            }
            $StudentTeacherID = $class->ClassTeacherID;

            // Create student
            $student = students::create([
                'StudentUserID' => $userId,
                'StudentClassID' => $request->input('StudentClassID'),
                'StudentDOB' => $request->input('StudentDOB'),
                'StudentGender' => $request->input('StudentGender'),
                'StudentCNIC' => $request->input('StudentCNIC'),
                'StudentPhoneNumber' => $request->input('StudentPhoneNumber'),
                'StudentHomeAddress' => $request->input('StudentHomeAddress'),
                'StudentReligion' => $request->input('StudentReligion'),
                'StudentMonthlyFee' => $request->input('StudentMonthlyFee'),
                'StudentTeacherID' => $StudentTeacherID
            ]);

            $StudentID = $student->id;

            // Create parent
            $parent = parents::create([
                'StudentID' => $StudentID,
                'FatherName' => $request->input('FatherName'),
                'MotherName' => $request->input('MotherName'),
                'GuardiansCNIC' => $request->input('GuardiansCNIC'),
                'GuardiansPhoneNumber' => $request->input('GuardiansPhoneNumber'),
                'GuardiansPhoneNumber2' => $request->input('GuardiansPhoneNumber2'),
                'HomeAddress' => $request->input('HomeAddress'),
                'GuardiansEmail' => $request->input('GuardiansEmail')
            ]);

            if (!$parent) {
                throw new \Exception('Failed to create parent record');
            }

            // Commit transaction
            DB::commit();

            // Send email
            $Url = 'http://localhost:3000/login?email=' . urlencode($email) . '&password=' . urlencode($password);
            $details = [
                'title' => 'Successfully Added a new Student',
                'body' => 'To login into your Student account please enter the following password',
                'password' => $password,
                'Url' => $Url
            ];

            Mail::to($email)->send(new \App\Mail\passwordSender($details));

            return response()->json(['success' => true, 'message' => "Please check your email for Activation of account."]);

        } catch (\Exception $e) {
            // Rollback transaction
            DB::rollBack();

            // Log error
            \Log::error('Failed to create student: ' . $e->getMessage());

            // Delete user if created
            if (isset($userId)) {
                $user = users::find($userId);
                if ($user) {
                    $user->delete();
                }
            }

            return ReturnData(false,$e->getMessage(),'Sorry, Something went wrong!');
        }
    }



    public function GetStudentAttendance(Request $request)
    {
        $user = $request->user();
        $ID = $user->id;
        $attendance = attendance::where('UsersID', $ID)->get();
        $presentCount = $attendance->where('attendance', 'Present')->count();
        $absentCount = $attendance->where('attendance', 'Absent')->count();
        return response()->json(['success' => true , 'presentCount' => $presentCount , 'absentCount' => $absentCount , 'attendance' => $attendance]);
    }


    public function GetStudentWeekAttendance(Request $request)
    {
        $user = $request->user();
        if ($user->role !== "Admin") {
            return ReturnData(false,'','Only admin can access this');
        }
        $today = Carbon::today();
        $lastWeek = Carbon::today()->subWeek()->format('Y-m-d');

        // Retrieve attendance records from the last week excluding today
        $attendance = attendance::whereBetween('Date', [$lastWeek, $today->copy()->subDay()->format('Y-m-d')])->get();

        $groupedAttendance = $attendance->groupBy('Date');

        $summary = [];

        foreach ($groupedAttendance as $date => $records) {
            $dayName = Carbon::parse($date)->format('l');
            
            // Exclude Sundays
            if ($dayName == 'Sunday') {
                continue;
            }

            $presentCount = $records->where('attendance', 'Present')->count();
            $absentCount = $records->where('attendance', 'Absent')->count();

            $summary[] = [
                'date' => $date,
                'day_name' => $dayName,
                'present_count' => $presentCount,
                'absent_count' => $absentCount
            ];
        }
        return ReturnData(true,$summary,'');
    }

    
    public function GetStudentClassDetailInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ClassRank' => 'required',
            'ClassName' => 'required',
        ]);
        if ($validator->fails()) {
            return ReturnData(false,'',$validator->errors());
        } else {
            $ClassRank = $request->input('ClassRank');
            $ClassName = $request->input('ClassName');
            $Class = classes::where('ClassRank', $ClassRank)
                ->where('ClassName', $ClassName)
                ->first();
            if ($Class) {
                $students = $Class->students()->with('users', 'parents', 'classes', 'teachers.users')->get();
                if ($students) {
                    foreach ($students as $student) {
                        if (isset($student->users->images[0])) {
                            $imgPath = $student->users->images[0]->ImageName;
                            $data = base64_encode(file_get_contents(public_path($imgPath)));
                            $student->users->images[0]->setAttribute('data', $data);
                        }
                    }
                    return ReturnData(true,$students,'');
                } else {
                    return ReturnData(false,'','Student not found!');
                }
            } else {
                return ReturnData(false,'','Class not found');
            }
        }
    }





    public function GetStudentInformation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ClassRank' => 'required',
            'ClassName' => 'required',
        ]);
        if ($validator->fails()) {
            return ReturnData(false,'',$validator->errors());
        } else {
            $ClassRank = $request->input('ClassRank');
            $ClassName = $request->input('ClassName');
            $Class = classes::where('ClassRank', $ClassRank)
                ->where('ClassName', $ClassName)
                ->first();
            if ($Class) {
                $students = $Class->students()->with('users.images','subjects:id,UsersID,SubjectName', 'parents')->get();
                if ($students) {
                    foreach ($students as $student) {
                        if (isset($student->users->images[0])) {
                            $imgPath = $student->users->images[0]->ImageName;
                            $data = base64_encode(file_get_contents(public_path($imgPath)));
                            $student->users->images[0]->setAttribute('data', $data);
                        }
                    }
                    return ReturnData(true,$students,'');
                } else {
                    return ReturnData(false,'','Student not found!');
                }
            } else {
                return ReturnData(false,'','Class not found!');
            }
        }
    }
    public function Delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ID' => 'required',
        ]);
        if ($validator->fails()) {
            return ReturnData(false,'',$validator->errors());
        } else {
            $user = $request->user();

            if ($user->role == "Admin") {
                $ID = $request->input('ID');
                $student = students::find($ID);

                if (!$student) {
                    return response()->json(['success' => false, 'message' => 'Student not found'], 404);
                }

                $UserID = $student->StudentUserID;
                $user = users::find($UserID);
                $image = images::where('UsersID', $UserID)->first();

                if (!$image) {
                    \Log::error('Image not found for user ID: ' . $UserID);
                    return response()->json(['success' => false, 'message' => 'Image not found'], 404);
                }

                $imagePath = $image->ImageName;
                $fullImagePath = public_path($imagePath);

                if (file_exists($fullImagePath)) {
                    // Delete the image file
                    if (unlink($fullImagePath)) {
                        \Log::info('Image file deleted successfully: ' . $fullImagePath);
                        $image->delete();

                        if ($student) {
                            $user->delete();
                            $response = [
                                'success' => true,
                                'message' => "Successfully deleted"
                            ];
                            return response()->json($response);
                        } else {
                            return response()->json(['success' => false, 'message' => 'Student not found']);
                        }

                    } else {
                        if ($student) {
                            $student->delete();
                            $response = [
                                'success' => true,
                                'message' => "Successfully deleted"
                            ];
                            return response()->json($response);
                        } else {
                            return response()->json(['success' => false, 'message' => 'Student not found']);
                        }
                    }
                } else {
                    \Log::error('Image file not found on disk: ' . $fullImagePath);
                    return response()->json(['success' => false, 'message' => 'Image not found on disk'], 404);
                }


            } else {
                $response = [
                    'success' => false,
                    'message' => "Only Admin Can Delete Class"
                ];
                return response()->json($response);
            }
        }
    }



    public function UpdateStudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ID' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'userName' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'StudentDOB' => 'required|date',
            'StudentGender' => 'required|string',
            'StudentCNIC' => 'required|string|max:255',
            'subjects' => 'required|array',
            'StudentClassID' => 'required|exists:classes,id',
            'StudentPhoneNumber' => 'required|string|max:125',
            'StudentHomeAddress' => 'required|string|max:255',
            'StudentReligion' => 'required|string|max:255',
            'StudentMonthlyFee' => 'required|max:255',
            'FatherName' => 'required|string|max:255',
            'MotherName' => 'required|string|max:255',
            'GuardiansCNIC' => 'required|string|max:255',
            'GuardiansPhoneNumber' => 'required|string|max:255',
            'GuardiansPhoneNumber2' => 'nullable|string|max:255',
            'HomeAddress' => 'required|string|max:255',
            'GuardiansEmail' => 'required|email|max:255',
            'image' => 'required|string' // Assuming image is base64 encoded
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()], 400);
        }

        $user = $request->user();
        if ($user->role != "Admin") {
            return response()->json(['success' => false, 'message' => "Only Admin Can Update Student"], 403);
        }

        $ID = $request->input('ID');
        $user = users::where('id', $ID)->first();

        if ($user) {
            $user->update([
                'name' => $request->input('name'),
                'userName' => $request->input('userName'),
                'role' => "Student",
                'email' => $request->input('email'),
            ]);

            $subjects = $request->input('subjects');
            subjects::where('UsersID', $ID)->delete(); // Remove old subjects
            foreach ($subjects as $subject) {
                subjects::create([
                    'UsersID' => $ID,
                    'SubjectName' => $subject
                ]);
            }

            // Handle image upload
            $pic = $request->input('image');
            if ($pic) {
                $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $pic));
                if ($imageData === false) {
                    return response()->json(['success' => false, 'message' => 'Failed to decode image data'], 400);
                }

                $extension = image_type_to_extension(exif_imagetype($pic));
                $filename = uniqid() . $extension;
                $storagePath = 'images/';
                $savePath = public_path($storagePath . $filename);

                if (file_put_contents($savePath, $imageData) === false) {
                    return response()->json(['success' => false, 'message' => 'Failed to save image file'], 500);
                }

                $PrevImage = images::where('UsersID', $ID)->first();
                $PrevImagePath = $PrevImage->ImageName;

                $fullImagePath = public_path($PrevImagePath);

                if (file_exists($fullImagePath)) {
                    if (unlink($fullImagePath)) {
                        \Log::info('Image file deleted successfully: ' . $fullImagePath);
                        images::updateOrCreate(['UsersID' => $ID], ['ImageName' => $storagePath . $filename]);
                    }
                }
            }

            $class = classes::find($request->input('StudentClassID'));
            if ($class) {
                $student = students::where('StudentUserID', $ID)->first();
                if ($student) {
                    $student->update([
                        'StudentClassID' => $class->id,
                        'StudentDOB' => $request->input('StudentDOB'),
                        'StudentGender' => $request->input('StudentGender'),
                        'StudentCNIC' => $request->input('StudentCNIC'),
                        'StudentPhoneNumber' => $request->input('StudentPhoneNumber'),
                        'StudentHomeAddress' => $request->input('StudentHomeAddress'),
                        'StudentReligion' => $request->input('StudentReligion'),
                        'StudentMonthlyFee' => $request->input('StudentMonthlyFee'),
                        'StudentTeacherID' => $class->ClassTeacherID
                    ]);

                    parents::updateOrCreate(
                        ['StudentID' => $student->id],
                        [
                            'FatherName' => $request->input('FatherName'),
                            'MotherName' => $request->input('MotherName'),
                            'GuardiansCNIC' => $request->input('GuardiansCNIC'),
                            'GuardiansPhoneNumber' => $request->input('GuardiansPhoneNumber'),
                            'GuardiansPhoneNumber2' => $request->input('GuardiansPhoneNumber2'),
                            'HomeAddress' => $request->input('HomeAddress'),
                            'GuardiansEmail' => $request->input('GuardiansEmail')
                        ]
                    );

                    return response()->json(['success' => true, 'message' => "Successfully Updated Student Information"]);
                }
            }
        }

        return response()->json(['success' => false, 'message' => "Sorry! Something went wrong. Please try again later."]);
    }



    public function ResetPassword(Request $request){
        $ID = $request->query('ID');
        $users = users::find($ID);
        $user = $request->user();
        if($user->role == 'Admin'){
            $password = Str::random(12);
            $BcryptPassword = bcrypt($password);
            $users->password = $BcryptPassword;
            $users->save();
            $Url = 'http://localhost:3000/login?email=' . urlencode($users->email) . '&password=' . urlencode($password);
            $details = [
                'title' => 'Successfully Added a new Student',
                'body' => 'To login into your Student account please enter the following password',
                'password' => $password,
                'Url' => $Url
            ];
            Mail::to($users->email)->send(new \App\Mail\passwordSender($details));
            return response()->json(['success' => true, 'message' => "Please Check your Email for Password"]);
        }
        else{
            return response()->json(['success' => false, 'message' => "Only Admin Can Update Student"], 403);
        }
    }


    public function studentattendance(Request $request)
    {
        $ClassRank = $request->input('ClassRank');
        $ClassName = $request->input('ClassName');
        $selectedRows = $request->input('selectedRows');

        $Class = classes::where('ClassName', $ClassName)
            ->where('ClassRank', $ClassRank)
            ->first();

        if (!$Class) {
            return response()->json(['success' => false, 'message' => 'Class not found']);
        }

        $ID = $Class->id;
        $students = students::where('StudentClassID', $ID)->get();
        $user = $request->user();

        if ($user->role == "Admin" || $user->role == "Teacher") {
            $date = date('Y-m-d');
            $studentIds = $students->pluck('StudentUserID')->toArray();



            // Find matched IDs
            $matchedIds = array_intersect($selectedRows, $studentIds);

            // Debugging: log matched IDs
            \Log::info('Matched IDs:', $matchedIds);

            // Process Present students
            foreach ($matchedIds as $matchedId) {
                $student = students::where('StudentUserID', $matchedId)
                    ->with('users', 'parents')
                    ->first();
                if ($student) {
                    $attendance = Attendance::updateOrCreate(
                        ['UsersID' => $student->StudentUserID, 'Date' => $date], // Search criteria
                        ['attendance' => 'Present'] // Update or create data
                    );

                    $parentEmail = $student->parents->GuardiansEmail;
                    $details = [
                        'title' => 'Student Attendance Report',
                        'body' => 'Your Child ' . $student->users->name . ' was Present today. No need to worry about it.',
                        'attendance' => 'Present',
                        'Student' => $student
                    ];

                    try {
                        Mail::to($parentEmail)->send(new \App\Mail\attendance($details));
                    } catch (\Exception $e) {
                        \Log::error('Mail sending error: ' . $e->getMessage());
                    }
                }
            }

            \Log::info('Selected Rows:', $selectedRows);
            \Log::info('Student IDs:', $studentIds);

            try {
                $mismatchedIds = array_filter($studentIds, function ($id) use ($matchedIds) {
                    return !in_array($id, $matchedIds);
                });

                \Log::info('Mismatched IDs:', $mismatchedIds);
            } catch (\Exception $e) {
                \Log::error('Error comparing IDs: ' . $e->getMessage());
                // Handle or rethrow the exception as needed
            }



            foreach ($mismatchedIds as $mismatchedId) {
                $student = students::where('StudentUserID', $mismatchedId)
                    ->with('users', 'parents')
                    ->first();
                if ($student) {
                    $attendance = Attendance::updateOrCreate(
                        ['UsersID' => $student->StudentUserID, 'Date' => $date], // Search criteria
                        ['attendance' => 'Absent'] // Update or create data
                    );
                    $parentEmail = $student->parents->GuardiansEmail;
                    $details = [
                        'title' => 'Student Attendance Report',
                        'body' => 'Your Child ' . $student->users->name . ' was Absent today. If you are unaware of this, please contact the school administrator.',
                        'attendance' => 'Absent',
                        'Student' => $student
                    ];
                    try {
                        Mail::to($parentEmail)->send(new \App\Mail\attendance($details));
                    } catch (\Exception $e) {
                        \Log::error('Mail sending error: ' . $e->getMessage());
                    }
                }
            }


            return response()->json(['success' => true, 'message' => 'Attendance records updated successfully']);
        } else {
            return response()->json(['success' => false, 'message' => 'Unauthorized']);
        }
    }


    public function MarkEachAttendance(Request $request){
        $UsersID = $request->query('ID');

        $date = date('Y-m-d');
        $attendance = attendance::updateOrCreate([
            'UsersID' => $UsersID,
            'Date' => $date,
        ],
        [
            'attendance' => 'Present',
        ]);
        if($attendance)
        {
            return ReturnData(true,'','Attendance Mark Successfully!');
        }
        else{
            return ReturnData(false,'','Cannot Mark Attendance');
        }
    }
    

    public function GetTodayattendance(Request $request){
        $user = $request->user();
        if(($user->role == "Student")){
            return ReturnData(false,'','you do not have access to this route');
        }
        $ClassRank = $request->input('ClassRank');
        $ClassName = $request->input('ClassName');
        $Class = classes::where('ClassName', $ClassName)
            ->where('ClassRank', $ClassRank)
            ->first();

        if (!$Class) {
            return response()->json(['success' => false, 'message' => 'Class not found']);
        }

        $ID = $Class->id;
        $students = students::where('StudentClassID', $ID)->get();
        $studentIds = $students->pluck('StudentUserID')->toArray();
        $date = date('Y-m-d');
        $attendance = attendance::whereIn('UsersID', $studentIds)->where('Date', $date)->where('attendance','Present')->select('id','UsersID')->get();
        $presentIds = $attendance->pluck('UsersID')->toArray();
        if($presentIds)
        {
            return ReturnData(true,$presentIds,'');
        }
        else{
            return ReturnData(true,[],'');
        }
    }


    public function GetStudentData(Request $request)
    {
        $ID = $request->query('ID');
        $user = $request->user();
        if ($user->role == "Admin") {
            $students = students::where('StudentUserID', $ID)
                ->with(['users.images', 'users.subjects', 'parents'])
                ->get();
            if ($students) {
                foreach ($students as $student) {
                    if (isset($student->users->images[0])) {
                        $imgPath = $student->users->images[0]->ImageName;
                        $data = base64_encode(file_get_contents(public_path($imgPath)));
                        $student->users->images[0]->setAttribute('data', $data);
                    }
                }
                return response()->json(['success' => true, 'data' => $students]);
            } else {
                return response()->json(['success' => false, 'message' => 'Student not found']);
            }
        } else {
            $response = [
                'success' => false,
                'message' => "Only Admin Can Edit Class"
            ];
            return response()->json($response);
        }
    }
}
