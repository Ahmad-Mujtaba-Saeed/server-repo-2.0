<?php

namespace App\Http\Controllers;

use App\Models\teachers;
use App\Rules\CheckTimeOverLap;
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
            $timetable = \App\Models\timetable::create([
                'ClassID' => $request->input('classId'),
                'Subject' => $request->input('subject'),
                'TeacherID' => null,
                'StartingTime' => $request->input('startTime'),
                'EndingTime' => $request->input('endTime'),
                'Day' => $request->input('day'),
            ]);
            if ($timetable) {
                return Response()->json(['success' => true, 'message' => 'Successfully Created time table']);
            } else {
                return Response()->json(['success' => false, 'message' => 'Failed to create time table']);
            }
        } else if ($user->role == 'Teacher') {
            $teacher = teachers::with('classes')->where('TeacherUserID', $ID)->first();
            if ($teacher->classes->id == $request->ClassID) {
                $timetable = \App\Models\timetable::create([
                    'ClassID' => $request->input('classId'),
                    'Subject' => $request->input('subject'),
                    'TeacherID' => null,
                    'StartingTime' => $request->input('startTime'),
                    'EndingTime' => $request->input('endTime'),
                    'Day' => $request->input('day'),
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
            'classId' => 'required|integer|exists:classes,id',
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
            $timetable = \App\Models\timetable::create([
                'ClassID' => $request->input('classId'),
                'Subject' => $request->input('subject'),
                'TeacherID' => $request->input('teacherId'),
                'StartingTime' => $request->input('startTime'),
                'EndingTime' => $request->input('endTime'),
                'Day' => $request->input('day'),
            ]);
            if ($timetable) {
                return Response()->json(['success' => true, 'message' => 'Successfully Created time table']);
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
                    'StartingTime' => $request->input('startTime'),
                    'EndingTime' => $request->input('endTime'),
                    'Day' => $request->input('day'),
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
        if($user->role != 'Admin'){
            return response()->json(['success' => false, 'message' => 'Only admin can add expensive']);
        }
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
