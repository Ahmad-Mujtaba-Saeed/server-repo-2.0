<?php

namespace App\Http\Controllers;

use App\Models\announcement;
use Illuminate\Http\Request;
use Validator;

class AnnouncementController extends Controller
{
    public function create(Request $request){
        $validator = Validator::make($request->all(),[
            'heading' => 'required|max:500',
            'description' => 'required|max:10000',
            'teacher' => 'required',
            'student' => 'required',
        ]);
        if($validator->fails()){
            return ReturnData(false,'',$validator->errors());
        }
        $announcement = announcement::create([
            'heading' => $request->input('heading'),
            'description' => $request->input('description'),
            'teacher' => $request->input('teacher'),
            'student' => $request->input('student'),
        ]);
        if($announcement){
            return ReturnData(true,'','Successfully created announcement!');
        }
        else{
            return ReturnData(true,'','Failed to create announcement.');
        }
    }

    public function show(Request $request){
        $user = $request->user();
        if($user->role == 'Student'){
            $announcement = announcement::where('student',true)->first();
            if($announcement){
                return ReturnData(true,$announcement,'');
            }
            else{
                return ReturnData(true,'','Not Found for student');
            }
        }
        else if($user->role == 'Teacher'){
            $announcement = announcement::where('teacher',true)->first();
            if($announcement){
                return ReturnData(true,$announcement,'');
            }
            else{
                return ReturnData(true,'','Not Found for teacher');
            }
        }
        else{
            return ReturnData(false,'','No Role assigned');
        }
    }
    public function showAll(Request $request){
        $announcement = announcement::all();
        if($announcement){
            return ReturnData(true,$announcement,'');
        }
        else{
            return ReturnData(true,'','Failed to get announcement');
        }
    }
    public function destroy(Request $request){
        $user = $request->user();
        if($user->role == 'Admin'){
            $ID = $request->query('ID');

            if (!$ID) {
                return response()->json(['error' => 'ID parameter is required'], 400);
            }
            // Find the announcement by ID
            $announcement = Announcement::find($ID);
            if (!$announcement) {
                return response()->json(['error' => 'Announcement not found'], 404);
            }
            $announcement->delete();
            if($announcement){
                return ReturnData(true,'','deleted announcement successfully');
            }
            else{
                return ReturnData(true,'','Failed to delete announcement');
            }
        }
    }
}
