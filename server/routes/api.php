<?php

use App\Http\Controllers\Classess;
use App\Http\Controllers\student;
use App\Http\Controllers\teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['check.api.token'])->group(function () {

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        Route::get('/Hello', function (Request $request) {
            return response()->json("hello");
        });
        Route::controller(teacher::class)->group(function () {
            Route::post('/CreateTeacher', 'CreateTeacher');
            Route::get('/GetTeacher','GetTeacher');
            Route::get('/GetTeacherData','GetTeacherData');
            Route::post('/UpdateTeacher','UpdateTeacher');
            Route::post('/DeleteTeacher','Delete');
            Route::post('/GetTeacherInformation','GetTeacherInformation');
        });
        Route::controller(Classess::class)->group(function (){
            Route::post('/CreateClass','CreateClass');
            Route::post('/UpdateClass','UpdateClass');
            Route::get('/GetClasses','GetClasses');
            Route::post('/DeleteClass','Delete');
            Route::get('/GetClassData','GetClassData');
            
        });
        Route::controller(student::class)->group(function (){
            Route::post('/CreateStudent','CreateStudent');
            Route::post('/GetStudentInformation','GetStudentInformation');
            Route::post('/GetStudentClassDetailInfo','GetStudentClassDetailInfo');
            Route::post('/DeleteStudent','Delete');
            Route::post('/UpdateStudent','UpdateStudent');
            Route::get('/GetStudentData','GetStudentData');
        });
    });

    Route::get('/protected-route', function () {
        return response()->json(['message' => 'You have access']);
    });

    Route::controller(AuthController::class)->group(function () {
        Route::post('/login', 'login');
        Route::post('/register', 'register');
        Route::post('/forgotPassword', 'forgotPassword');
    });



    
    Route::get('/verify-email', [AuthController::class, 'verifyEmail'])->name('verify.email');

});



Route::get('/test', function () {
    return "test";
});
Route::get('/csrf-token', function () {
    return response()->json(['csrfToken' => csrf_token()]);
});