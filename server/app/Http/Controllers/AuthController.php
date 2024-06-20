<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Auth;
use App\Models\users;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'userName' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
            'role' => 'required'
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => $validator->errors()
            ];
            return response()->json($response);
        }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = users::create($input);

        $success['token'] = $user->createToken('MyApp')->plainTextToken;
        $success['Name'] = $user->name;
        $success['Role'] = $user->role;

        $response = [
            'success' => true,
            'data' => $success
        ];
        return response()->json($response, 200);
    }






    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => $validator->errors()
            ];
            return response()->json($response, 401);
        }
        if (Auth::guard('users')->attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::guard('users')->user();

            $success['token'] = $user->createToken('MyApp')->plainTextToken;
            $success['Name'] = $user->name;
            $success['Role'] = $user->role;

            return response()->json(['success' => true, 'data' => $success], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid Login Credentials']);
        }
    }





    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = $request->email;
        $user = users::where('email', '=', $email)->first();
        
        if ($user) {
            $user->verification_token = Str::random(60);
            if ($user->save()) {
                $verificationUrl = 'https://localhost:3000/ChangePassword?token=' . $user->verification_token . '&email=' . urlencode($user->email);
                $details = [
                    'title' => 'Forgot Password Request',
                    'body' => 'Please click the following link to reset your password for this email address!',
                    'verification_url' => $verificationUrl
                ];
                try {
                    Mail::to($email)->send(new \App\Mail\VerifyEmail($details));
                    return response()->json('Please check your email for Activation of account.');
                } catch (\Exception $e) {
                    return response()->json('Failed to send email. Please try again later.');
                }
            } else {
                return response()->json('Failed to save user data. Please try again later.');
            }
        } else {
            return response()->json('User not found.', 404);
        }
    }
}