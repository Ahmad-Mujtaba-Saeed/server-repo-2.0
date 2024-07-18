<?php

namespace App\Http\Controllers;

use App\Models\students;
use App\Models\teachers;
use App\Rules\CheckTimeOverLap;
use Carbon\Carbon;
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
        if($request->input('subject') == 'break'){
            $user = $request->user();
        $ID = $user->id;
        if ($user->role == 'Admin') {
            $timetable = \App\Models\timetable::updateOrCreate(    [
                'ClassID' => $request->input('classId'),
                'StartingTime' => $request->input('startTime'),
                'EndingTime' => $request->input('endTime'),
                'Day' => $request->input('day'),
            ],
            [
                'Subject' => $request->input('subject'),
                'TeacherID' => null, // Assuming you're passing a teacherId
            ]);
            if ($timetable) {
                return Response()->json(['success' => true, 'message' => 'Successfully Created time table']);
            } else {
                return Response()->json(['success' => false, 'message' => 'Failed to create time table']);
            }
        } else if ($user->role == 'Teacher') {
            $teacher = teachers::with('classes')->where('TeacherUserID', $ID)->first();
            if ($teacher->classes->id) {
                $timetable = \App\Models\timetable::updateOrCreate(    [
                    'ClassID' => $teacher->classes->id,
                    'StartingTime' => $request->input('startTime'),
                    'EndingTime' => $request->input('endTime'),
                    'Day' => $request->input('day'),
                ],
                [
                    'Subject' => $request->input('subject'),
                    'TeacherID' => null, // Assuming you're passing a teacherId
                ]);
                if ($timetable) {
                    return Response()->json(['success' => true, 'message' => 'Successfully Created time table']);
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
        else{
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'teacherId' => 'required|integer',
            'startTime' => 'required|date_format:H:i:s',
            'day' => 'required|string|max:255',
            'endTime' => [
                'required',
                'date_format:H:i:s',
                'after:startTime',
                new CheckTimeOverLap($request->input('teacherId'), $request->input('startTime'), $request->input('day')),
            ],
        ]);
        if ($validator->fails()) {
            return Response()->json(['success' => false, 'message' => $validator->errors()]);
        }
        }
        $user = $request->user();
        $ID = $user->id;
        if ($user->role == 'Admin') {
            $timetable = \App\Models\timetable::updateOrCreate(    [
                'ClassID' => $request->input('classId'),
                'StartingTime' => $request->input('startTime'),
                'EndingTime' => $request->input('endTime'),
                'Day' => $request->input('day'),
            ],
            [
                'Subject' => $request->input('subject'),
                'TeacherID' => $request->input('teacherId'), // Assuming you're passing a teacherId
            ]);
            if ($timetable) {
                return Response()->json(['success' => true, 'message' => 'Successfully Created time table']);
            } else {
                return Response()->json(['success' => false, 'message' => 'Failed to create time table']);
            }
        } else if ($user->role == 'Teacher') {
            $teacher = teachers::with('classes')->where('TeacherUserID', $ID)->first();
            if ($teacher->classes->id) {
                $timetable = \App\Models\timetable::updateOrCreate(    [
                    'ClassID' => $teacher->classes->id,
                    'StartingTime' => $request->input('startTime'),
                    'EndingTime' => $request->input('endTime'),
                    'Day' => $request->input('day'),
                ],
                [
                    'Subject' => $request->input('subject'),
                    'TeacherID' => $request->input('teacherId'), // Assuming you're passing a teacherId
                ]);
                if ($timetable) {
                    return Response()->json(['success' => true, 'message' => 'Successfully Created time table']);
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
    public function show(Request $request)
    {
        $user = $request->user();
        $ID = $user->id; 
        // if($user->role != 'Admin'){
        //     return response()->json(['success' => false, 'message' => 'Only admin can add expensive']);
        // }
        if($user->role == 'Teacher'){
            $teacher = teachers::with('classes')->where('TeacherUserID', $ID)->first();
            if ($teacher->classes->id) {
            $ClassID = $teacher->classes->id;
            $timetableData = \App\Models\timetable::where('ClassID', $ClassID)
            ->select('id', 'Subject', 'StartingTime', 'EndingTime', 'Day', 'TeacherID')
            ->get();
        
        // Group by StartingTime and EndingTime
        $groupedTimetable = $timetableData->groupBy(function ($item) {
            return $item->StartingTime . '-' . $item->EndingTime; // Group by both times
        });
        
        // Format the grouped data
        $formattedTimetable = [];
        foreach ($groupedTimetable as $timeGroup) {
            // Get the first entry to represent the group
            $firstEntry = $timeGroup->first();
        
            // Create a period entry
            $periodEntry = [
                'period' => [$firstEntry->StartingTime, $firstEntry->EndingTime],
                'Monday' => ['subject' => '', 'teacher_id' => ''],
                'Tuesday' => ['subject' => '', 'teacher_id' => ''],
                'Wednesday' => ['subject' => '', 'teacher_id' => ''],
                'Thursday' => ['subject' => '', 'teacher_id' => ''],
                'Friday' => ['subject' => '', 'teacher_id' => ''],
                'Saturday' => ['subject' => '', 'teacher_id' => '']
            ];
            // Populate the subjects and teacher IDs for each day
            foreach ($timeGroup as $entry) {
                switch ($entry->Day) {
                    case 'Monday':
                        $periodEntry['Monday']['subject'] = $entry->Subject;
                        $periodEntry['Monday']['teacher_id'] = $entry->TeacherID;
                        break;
                    case 'Tuesday':
                        $periodEntry['Tuesday']['subject'] = $entry->Subject;
                        $periodEntry['Tuesday']['teacher_id'] = $entry->TeacherID;
                        break;
                    case 'Wednesday':
                        $periodEntry['Wednesday']['subject'] = $entry->Subject;
                        $periodEntry['Wednesday']['teacher_id'] = $entry->TeacherID;
                        break;
                    case 'Thursday':
                        $periodEntry['Thursday']['subject'] = $entry->Subject;
                        $periodEntry['Thursday']['teacher_id'] = $entry->TeacherID;
                        break;
                    case 'Friday':
                        $periodEntry['Friday']['subject'] = $entry->Subject;
                        $periodEntry['Friday']['teacher_id'] = $entry->TeacherID;
                        break;
                    case 'Saturday':
                        $periodEntry['Saturday']['subject'] = $entry->Subject;
                        $periodEntry['Saturday']['teacher_id'] = $entry->TeacherID;
                        break;
                }
            }
        
            $formattedTimetable[] = $periodEntry;
        }
        
        // Return response
        if ($formattedTimetable) {
            return response()->json(['success' => true, 'data' => $formattedTimetable]);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to get timetable']);
        }
            }
        }
        else if($user->role == 'Student'){
            $StudentData = students::with('classes')->where('StudentUserID',$ID)->first();
            $ClassID = $StudentData->classes->id;
            $date = Carbon::today();
            $dayName = $date->format('l');
            $timetableData = \App\Models\timetable::where('ClassID', $ClassID)->where('Day',$dayName)
            ->select('id', 'Subject', 'StartingTime', 'EndingTime','TeacherID')
            ->get();
            
            return ReturnData(true,$timetableData,'');
        }
        else if ($user->role == 'Admin'){
        if($request->query('ID')){
            $ClassID =$request->query('ID');
            $timetableData = \App\Models\timetable::where('ClassID', $ClassID)
            ->select('id', 'Subject', 'StartingTime', 'EndingTime', 'Day', 'TeacherID')
            ->get();
        
        // Group by StartingTime and EndingTime
        $groupedTimetable = $timetableData->groupBy(function ($item) {
            return $item->StartingTime . '-' . $item->EndingTime; // Group by both times
        });
        
        // Format the grouped data
        $formattedTimetable = [];
        foreach ($groupedTimetable as $timeGroup) {
            // Get the first entry to represent the group
            $firstEntry = $timeGroup->first();
        
            // Create a period entry
            $periodEntry = [
                'period' => [$firstEntry->StartingTime, $firstEntry->EndingTime],
                'Monday' => ['subject' => '', 'teacher_id' => ''],
                'Tuesday' => ['subject' => '', 'teacher_id' => ''],
                'Wednesday' => ['subject' => '', 'teacher_id' => ''],
                'Thursday' => ['subject' => '', 'teacher_id' => ''],
                'Friday' => ['subject' => '', 'teacher_id' => ''],
                'Saturday' => ['subject' => '', 'teacher_id' => '']
            ];
            // Populate the subjects and teacher IDs for each day
            foreach ($timeGroup as $entry) {
                switch ($entry->Day) {
                    case 'Monday':
                        $periodEntry['Monday']['subject'] = $entry->Subject;
                        $periodEntry['Monday']['teacher_id'] = $entry->TeacherID;
                        break;
                    case 'Tuesday':
                        $periodEntry['Tuesday']['subject'] = $entry->Subject;
                        $periodEntry['Tuesday']['teacher_id'] = $entry->TeacherID;
                        break;
                    case 'Wednesday':
                        $periodEntry['Wednesday']['subject'] = $entry->Subject;
                        $periodEntry['Wednesday']['teacher_id'] = $entry->TeacherID;
                        break;
                    case 'Thursday':
                        $periodEntry['Thursday']['subject'] = $entry->Subject;
                        $periodEntry['Thursday']['teacher_id'] = $entry->TeacherID;
                        break;
                    case 'Friday':
                        $periodEntry['Friday']['subject'] = $entry->Subject;
                        $periodEntry['Friday']['teacher_id'] = $entry->TeacherID;
                        break;
                    case 'Saturday':
                        $periodEntry['Saturday']['subject'] = $entry->Subject;
                        $periodEntry['Saturday']['teacher_id'] = $entry->TeacherID;
                        break;
                }
            }
        
            $formattedTimetable[] = $periodEntry;
        }
        
        // Return response
        if ($formattedTimetable) {
            return response()->json(['success' => true, 'data' => $formattedTimetable]);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to get timetable']);
        }
    }
    } 
    else{
        return ReturnData(false,'','Cannot access this');
    }
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
    public function update(Request $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $user = $request->user();
        if($user->role =='Admin'){
            $ClassID = $request->query('ID');
            $timetable = \App\Models\timetable::where('ClassID',$ClassID)->get();
            foreach($timetable as $Stimetable){
                $Stimetable->delete();
            }
            if($timetable){
                return response()->json(['success' => true , 'message' => 'Successfully Deleted Timetable']);
            }else{
                return response()->json(['success' => true , 'message' => 'Failed to delete Timetable']);
            }
        }
        else if($user->role == 'Teacher'){
            
        }
    }
}
