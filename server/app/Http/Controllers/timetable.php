<?php

namespace App\Http\Controllers;

use App\Models\teachers;
use Illuminate\Http\Request;
use Response;
use Validator;

class timetable extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'classId' => 'required|integer|exists:classes,id',
            'subject' => 'required|string|max:255',
            'teacherId' => 'required|integer|exists:teachers,id',
            'startTime' => 'required|date_format:H:i:s',
            'endTime' => 'required|date_format:H:i:s|after:startTime',
            'day' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return Response()->json(['success' => false, 'message' => $validator->errors()]);
        }
        $user = $request->user();
        $ID = $user->id;
        if ($user->role == 'Admin') {
            $timetable = \App\Models\timetable::create([
                'ClassID' => $request->input('classId'),
                'Subject' => $request->input('subject'),
                'TeacherID' => $request->input('teacherId'),
                'StartingTime' => $request->input('startTime'),
                'EndingTime' =>  $request->input('endTime'),
                'Day' => $request->input('day'),
            ]);
            if ($timetable) {
                return Response()->json(['success' => false, 'message' => 'Successfully Created time table']);
            } else {
                return Response()->json(['success' => false, 'message' => 'Failed to create time table']);
            }
        } else if ($user->role == 'Teacher') {
            $teacher = teachers::with('classes')->where('TeacherUserID', $ID)->first();
            if ($teacher->classes->id == $request->ClassID) {
                $timetable = \App\Models\timetable::create([
                    'ClassID' => $request->input('classId'),
                    'Subject' => $request->input('subject'),
                    'TeacherID' => $request->input('teacherId'),
                    'StartingTime' =>  $request->input('startTime'),
                    'EndingTime' =>  $request->input('endTime'),
                    'Day' => $request->input('day'),
                ]);
                if ($timetable) {
                    return Response()->json(['success' => false, 'message' => 'Successfully Created time table']);
                } else {
                    return Response()->json(['success' => false, 'message' => 'Failed to create time table']);
                }
            } else {
                return Response()->json(['success' => false, 'message' => 'Teacher can only create time table of its own class']);
            }
        } else {
            return Response()->json(['success' => false, 'message' => 'Only admin can create time table']);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
