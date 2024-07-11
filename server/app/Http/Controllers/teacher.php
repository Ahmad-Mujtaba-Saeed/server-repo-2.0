<?php

namespace App\Http\Controllers;

use App\Models\classes;
use App\Models\images;
use App\Models\students;
use Hamcrest\TypeSafeDiagnosingMatcher;
use Illuminate\Http\Request;
use Response;
use Validator;
use Auth;
use App\Models\users;
use App\Models\subjects;
use App\Models\teachers;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class teacher extends Controller
{
    public function CreateTeacher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'userName' => 'required|unique:users|string|max:255',
            'email' => 'required|email|unique:users',
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
            throw new \Exception($validator->errors());
        }
    
        $user = $request->user();
    
        if ($user->role !== "Admin") {
            return response()->json([
                'success' => false,
                'message' => "Only Admin Can Create Teacher"
            ]);
        }
    
        DB::beginTransaction();
        try {
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
            foreach ($subjects as $subject) {
                subjects::create([
                    'UsersID' => $userId,
                    'SubjectName' => $subject
                ]);
            }
    
            $pic = $request->input('image');
            if (isset($pic)) {
                $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $pic));
    
                if ($imageData === false) {
                    throw new \Exception('Failed to decode image data');
                }
    
                $mimeType = mime_content_type($pic);
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
    
            $teacher = teachers::create([
                'TeacherUserID' => $userId,
                'TeacherDOB' => $request->input('TeacherDOB'),
                'TeacherCNIC' => $request->input('TeacherCNIC'),
                'TeacherPhoneNumber' => $request->input('TeacherPhoneNumber'),
                'TeacherHomeAddress' => $request->input('TeacherHomeAddress'),
                'TeacherReligion' => $request->input('TeacherReligion'),
                'TeacherSalary' => $request->input('TeacherSalary')
            ]);
    
            $Url = 'http://localhost:3000/login?email=' . urlencode($email) . '&password=' . urlencode($password);
            $details = [
                'title' => 'Successfully Added a new teacher',
                'body' => 'To login into your teacher account please enter the following password',
                'password' => $password,
                'Url' => $Url
            ];
    
            Mail::to($email)->send(new \App\Mail\passwordSender($details));
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => "Please check your email for Activation of account."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
    
            \Log::error('Error creating teacher: ' . $e->getMessage());
    
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
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
    
                if (!$teacher) {
                    return response()->json(['success' => false, 'message' => 'teacher not found'], 404);
                }
                
                $UserID = $teacher->TeacherUserID;
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
                        
                        if ($teacher && $user) {
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
                        if ($teacher) {
                            $teacher->delete();
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
                'message' => "Only Admin Can Delete Teacher"
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
            $PrevImage = images::where('UsersID' , $ID)->first();
            $PrevImagePath = $PrevImage->ImageName;

            $fullImagePath = public_path($PrevImagePath);
            
                if (file_exists($fullImagePath)) {
                    if (unlink($fullImagePath)) {
                        \Log::info('Image file deleted successfully: ' . $fullImagePath);
                        images::updateOrCreate(['UsersID' => $ID], ['ImageName' => $storagePath . $filename]);
                    }
                }
            
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


public function GetTeacherClassinfo(Request $request){
    $user = $request->user();
    if($user->role == "Teacher"){
        $data = teachers::where('TeacherUserID',$user->id)->with(['classes', 'users.subjects'])->get();
        $response = [
            'success' => true,
            'data' => $data
        ];
        return Response()->json($response);
    }
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
