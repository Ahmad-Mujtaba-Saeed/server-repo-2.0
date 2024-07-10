<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\Classess;
use App\Http\Controllers\student;
use App\Http\Controllers\teacher;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VideoUploader;

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
            $user = $request->user();
            if ($user) {
                return response()->json(['success' => true, 'data' => $user]);
            } else {
                return response()->json(['success' => false, 'message' => 'failed to fetch user data']);
            }
        });




        Route::controller(teacher::class)->group(function () {
            Route::post('/CreateTeacher', 'CreateTeacher');
            Route::get('/GetTeacher', 'GetTeacher');
            Route::get('/GetTeacherData', 'GetTeacherData');
            Route::post('/UpdateTeacher', 'UpdateTeacher');
            Route::post('/DeleteTeacher', 'Delete');
            Route::post('/GetTeacherInformation', 'GetTeacherInformation');
            Route::get('/GetTeacherClassinfo', 'GetTeacherClassinfo');
        });
        Route::controller(Classess::class)->group(function () {
            Route::post('/CreateClass', 'CreateClass');
            Route::post('/UpdateClass', 'UpdateClass');
            Route::get('/GetClasses', 'GetClasses');
            Route::post('/DeleteClass', 'Delete');
            Route::get('/GetClassData', 'GetClassData');
        });
        Route::controller(student::class)->group(function () {
            Route::post('/CreateStudent', 'CreateStudent');
            Route::post('/GetStudentInformation', 'GetStudentInformation');
            Route::post('/GetStudentClassDetailInfo', 'GetStudentClassDetailInfo');
            Route::post('/DeleteStudent', 'Delete');
            Route::post('/UpdateStudent', 'UpdateStudent');
            Route::get('/GetStudentData', 'GetStudentData');
            Route::post('/studentattendance', 'studentattendance');
            Route::get('/GetStudentAttendance','GetStudentAttendance');
        });
        Route::controller(VideoUploader::class)->group(function () {
            Route::post('/upload-video', 'Store');
            Route::get('/show-video', 'Show');
            Route::get('/show-video-info', 'ShowInfo');
            Route::get('/showvideoDataPic', 'ShowVideoPicWData');
            Route::get('/VideoInfo', 'GetVideoInfo');
            Route::get('/destroy-video', 'Destroy');
            Route::post('/Create-playlist', 'CreatePlaylist');
            Route::get('/PlaylistData', 'PlaylistData');
            Route::get('/GetplaylistData', 'GetplaylistData');
            Route::post('/UploadComment', 'UploadComment');
        });
        Route::controller(ChatController::class)->group(function () {
            Route::post('/PrivateMessage', 'PrivateMessage');
            Route::post('/MessageStoredData', 'MessageStore');
            Route::get('/GetEachStoredMessages', 'GetEachStoredMessages');
        });
    });

    Route::controller(AuthController::class)->group(function () {
        Route::post('/login', 'login');
        Route::post('/register', 'register');
        Route::post('/forgotPassword', 'forgotPassword');
    });

    Route::get('/verify-email', [AuthController::class, 'verifyEmail'])->name('verify.email');
});



Route::post('/SendMessage', [ChatController::class, 'sendMessage']);
Route::get('/csrf-token', function () {
    return response()->json(['csrfToken' => csrf_token()]);
});