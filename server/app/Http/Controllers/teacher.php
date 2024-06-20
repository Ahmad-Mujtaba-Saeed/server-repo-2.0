<?php

namespace App\Http\Controllers;

use App\Models\classes;
use App\Models\images;
use App\Models\students;
use Illuminate\Http\Request;
use Validator;
use Auth;
use App\Models\users;
use App\Models\subjects;
use App\Models\teachers;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class teacher extends Controller
{
    public function CreateTeacher(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'userName' => 'required|string|max:255',
            'email' => 'required|email',
            'TeacherDOB' => 'required|date',
            'subjects' => 'required',
            'TeacherCNIC' => 'required|string',
            'TeacherPhoneNumber' => 'required|string|max:255',
            'TeacherHomeAddress' => 'required',
            'TeacherReligion' => 'required',
            'TeacherSalary' => 'required',
            'image' => 'required'
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => $validator->errors()
            ];
            return response()->json($response);
        }

        $user = $request->user();

        if($user->role == "Admin"){
        $email = $request->input('email');
        $password = Str::random(12);
        $BcryptPassword = bcrypt($password);
        $role = "Teacher";
        $user = users::create([
            'name' => $request->input('name'),
            'userName' => $request->input('userName'),
            'role' => $role,
            'email' => $request->input('email'),
            'password' => $BcryptPassword
        ]);
        $userId = $user->id;
        $subjects = $request->input('subjects');
        foreach($subjects as $subject){
            $subjectResult = subjects::create([
                'UsersID' => $userId,
                'SubjectName' => $subject
            ]);
        }
        $pic = $request->input('image');
        if (isset ($pic)) {
            // Ensure that an image was uploaded
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $pic));

            if ($imageData === false) {
                return response()->json(['success' => false , 'message' => 'Failed to decode image data'], 400);
            }
            $mimeType = mime_content_type($pic);

            $extension = image_type_to_extension(exif_imagetype($pic));

            $filename = uniqid() . $extension; // You can adjust the filename extension based on the image format

            $storagePath = 'images/';

            $savePath = public_path($storagePath . $filename);
            if (file_put_contents($savePath, $imageData) === false) {
                return response()->json(['success' => false , 'message' => 'Failed to save image file'], 500);
            }

            // Assuming you want to associate the image path with the newly created record
            $image = new images();
            $image->UsersID = $userId;
            $image->ImageName = $storagePath . $filename; // Store the image path
            $image->save();
        }
        $teacher = teachers::create([
            'TeacherUserID' => $userId,
            'TeacherDOB' => $request->input('TeacherDOB'),
            'TeacherCNIC' => $request->input('TeacherCNIC'),
            'TeacherPhoneNumber' => $request->input('TeacherPhoneNumber'),
            'TeacherHomeAddress' => $request->input('TeacherHomeAddress'),
            'TeacherReligion' => $request->input('TeacherReligion'),
            'TeacherSalary' => $request->input('TeacherSalary')
        ]);
        if($teacher){
            $Url = 'http://localhost:3000/login?email=' . urlencode($email) . '&password=' . urlencode($password);
                $details = [
                    'title' => 'Successfully Added a new teacher',
                    'body' => 'To login into your teacher account please enter the following password',
                    'password' => $password,
                    'Url' => $Url
                ];
                try {
                    Mail::to($email)->send(new \App\Mail\passwordSender($details));
                    $response = [
                        'success' => true,
                        'message' => "Please check your email for Activation of account."
                    ];
                    return response()->json($response);
                } catch (\Exception $e) {
                    $response = [
                        'success' => true,
                        'message' => "Failed to send email. Please try again later."
                    ];
                    return response()->json($response);
                }
        }
        else{
            $response = [
                'success' => false,
                'message' => "Sorry! Something went wrong. Please try again later."
            ];
            $user = users::find($userId);
            $user->delete();
            return response()->json($response);
        }
    }
    else{
        $response = [
            'success' => false,
            'message' => "Only Admin Can Create Teacher"
        ];
        return response()->json($response);
    }
    
}






public function Delete(Request $request)
{
    $validator = Validator::make($request->all(), [
        'ID' => 'required',
    ]);
    if ($validator->fails()) {
        $response = [
            'success' => false,
            'message' => $validator->errors()
        ];
        return response()->json($response);
    } else {
        $user = $request->user();

        if ($user->role == "Admin") {
            $ID = $request->input('ID');
            $teacher = teachers::find($ID);

            if ($teacher) {
                $teacher->delete();
                    $response = [
                        'success' => true,
                        'message' => "Successfully deleted"
                    ];
                    return response()->json($response);
            } else {
                return response()->json(['success' => false, 'message' => 'Class not found']);
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



public function UpdateTeacher(Request $request)
{
    $validator = Validator::make($request->all(), [
        'ID' => 'required|exists:users,id',
        'name' => 'required|string|max:255',
        'userName' => 'required|string|max:255',
        'email' => 'required|email',
        'TeacherDOB' => 'required|date',
        'subjects' => 'required',
        'TeacherCNIC' => 'required|string',
        'TeacherPhoneNumber' => 'required|string|max:255',
        'TeacherHomeAddress' => 'required',
        'TeacherReligion' => 'required',
        'TeacherSalary' => 'required',
        'image' => 'required'
    ]);

    if ($validator->fails()) {
        return response()->json(['success' => false, 'message' => $validator->errors()], 400);
    }

    $user = $request->user();
    if ($user->role != "Admin") {
        return response()->json(['success' => false, 'message' => "Only Admin Can Update Teacher"], 403);
    }

    $ID = $request->input('ID');
    $user = users::where('id', $ID)->first();

    if ($user) {
        $user->update([
            'name' => $request->input('name'),
            'userName' => $request->input('userName'),
            'role' => "Teacher",
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

            images::updateOrCreate(['UsersID' => $ID], ['ImageName' => $storagePath . $filename]);
        }

            $teacher = teachers::where('TeacherUserID', $ID)->first();
            if ($teacher) {
                $teacher->update([
                    'TeacherUserID' => $ID,
                    'TeacherDOB' => $request->input('TeacherDOB'),
                    'TeacherCNIC' => $request->input('TeacherCNIC'),
                    'TeacherPhoneNumber' => $request->input('TeacherPhoneNumber'),
                    'TeacherHomeAddress' => $request->input('TeacherHomeAddress'),
                    'TeacherReligion' => $request->input('TeacherReligion'),
                    'TeacherSalary' => $request->input('TeacherSalary')
                ]);

                return response()->json(['success' => true, 'message' => "Successfully Updated Student Information"]);
            }
        
    }

    return response()->json(['success' => false, 'message' => "Sorry! Something went wrong. Please try again later."]);
}




public function GetTeacherInformation(Request $request){
    $user = $request->user();
    if($user->role == "Admin") {
        $ClassRank = $request->input('ClassRank');
        $ClassName = $request->input('ClassName');
        if($ClassRank == "" && $ClassName == ""){
            $teachers = teachers::with(['users.images','classes', 'users.subjects'])
            ->get();
            if ($teachers) {
                foreach ($teachers as $teacher) {
                    if (isset($teacher->users->images[0])) {
                        $imgPath = $teacher->users->images[0]->ImageName;
                        $data = base64_encode(file_get_contents(public_path($imgPath)));
                        $teacher->users->images[0]->setAttribute('data', $data);
                    }
                }
                return response()->json(['success' => true, 'data' => $teachers]);
            } else {
                return response()->json(['success' => false, 'message' => 'teacher not found']);
            }
        }
        else{
            $Class = classes::where('ClassRank', $ClassRank)
            ->where('ClassName', $ClassName)
            ->first();
        if ($Class) {
            $teachers = $Class->teachers()->with('users.images','classes')->get();
            if ($teachers) {
                foreach ($teachers as $teacher) {
                    if (isset($teacher->users->images[0])) {
                        $imgPath = $teacher->users->images[0]->ImageName;
                        $data = base64_encode(file_get_contents(public_path($imgPath)));
                        $teacher->users->images[0]->setAttribute('data', $data);
                    }
                }
                return response()->json(['success' => true, 'data' => $teachers]);
            } else {
                return response()->json(['success' => false, 'message' => 'teacher not found']);
            }
        }
    }
    }
    else{
        $response = [
            'success' => false,
            'message' => "Only Admin Can Edit Class"
        ];
        return response()->json($response);
    }
}



    public function GetTeacherData(Request $request){
        $ID = $request->query('ID');
        $user = $request->user();

        if($user->role == "Admin") {
            $teachers = teachers::where('TeacherUserID', $ID)
                    ->with(['users.images', 'users.subjects'])
                    ->get();
                if ($teachers) {
                    foreach ($teachers as $teacher) {
                        if (isset($teacher->users->images[0])) {
                            $imgPath = $teacher->users->images[0]->ImageName;
                            $data = base64_encode(file_get_contents(public_path($imgPath)));
                            $teacher->users->images[0]->setAttribute('data', $data);
                        }
                    }
                    return response()->json(['success' => true, 'data' => $teacher]);
                } else {
                    return response()->json(['success' => false, 'message' => 'teacher not found']);
                }
        } else {
            $response = [
                'success' => false,
                'message' => "Only Admin Can Edit Class"
            ];
            return response()->json($response);
        }
    }



    public function GetTeacher(){
        $teachers = teachers::with('users','classes')->get();
        if($teachers){
        $response = [
            'success' => true,
            'data' => $teachers
        ];
        return response()->json($response); 
        }       
    }
}
