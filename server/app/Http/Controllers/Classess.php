<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\classes;
use Illuminate\Validation\Rule;


class Classess extends Controller
{
    public function CreateClass(Request $request)
    {
        $validator = Validator::make($request->all(), [
'ClassRank' => 'required|string|max:255',
'ClassName' => [
    'required',
    'string',
    'max:255',
    Rule::unique('classes')->where(function ($query) {
        return $query->where('ClassRank', request('ClassRank'));
    }),
],
            'ClassFloor' => 'required',
            'ClassTeacherID' => 'required'
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => $validator->errors()
            ];
            return response()->json($response);
        }
        $user = $request->user();

        if ($user->role == "Admin") {
            $input = request()->all();
            $class = classes::create($input);
            if ($class) {
                $response = [
                    'success' => true,
                    'data' => $class
                ];
                return response()->json($response);
            } else {
                $response = [
                    'success' => false,
                    'message' => "Cannot create class Successfully"
                ];
                return response()->json($response);
            }
        } else {
            $response = [
                'success' => false,
                'message' => "Only Admin Can Create Class"
            ];
            return response()->json($response);
        }
    }


    public function UpdateClass(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ClassName' => 'required|string|max:255',
            'ClassRank' => 'required|string|max:255',
            'ClassFloor' => 'required',
            'ClassTeacherID' => 'required',
            'ClassID' => 'required'
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => $validator->errors()
            ];
            return response()->json($response);
        }
        $user = $request->user();

        if ($user->role == "Admin") {
            $ClassID = $request->input('ClassID');
            $input = $request->except('ClassID'); // Exclude the ClassID from the input data
            $class = classes::findOrFail($ClassID); // Find the class by its ID
            $done = $class->update($input);            
            if ($done) {
                $response = [
                    'success' => true,
                    'data' => $class
                ];
                return response()->json($response);
            } else {
                $response = [
                    'success' => false,
                    'message' => "Class Updated Successfully"
                ];
                return response()->json($response);
            }
        } else {
            $response = [
                'success' => false,
                'message' => "Only Admin Can Update Class"
            ];
            return response()->json($response);
        }
    }



    public function GetClasses()
    {
        $Classes = classes::with('teachers.users')->get();
        if ($Classes) {
            $response = [
                'success' => true,
                'data' => $Classes
            ];
            return response()->json($response);
        } else {
            $response = [
                'success' => false,
                'message' => "Cannot Get any Class"
            ];
            return response()->json($response);
        }
    }



    public function GetClassData(Request $request)
    {
        $ID = $request->query('ID');
        $user = $request->user();
        if ($user->role == "Admin") {
            $class = classes::with('teachers.users')->find($ID);

            if ($class) {
                $response = [
                    'success' => true,
                    'data' => $class
                ];
                return response()->json($response);
            } else {
                return response()->json(['success' => false, 'message' => 'Class not found']);
            }
        } else {
            $response = [
                'success' => false,
                'message' => "Only Admin Can Edit Class"
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
                $class = classes::find($ID);
                if ($class) {
                    $class->delete();
                    $classes = classes::all();
                    if ($classes) {
                        $response = [
                            'success' => true,
                            'data' => $classes
                        ];
                        return response()->json($response);
                    }
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
}
