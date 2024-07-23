<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Classess;
use App\Http\Controllers\EnvController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\student;
use App\Http\Controllers\teacher;
use App\Http\Controllers\timetable;
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

    Route::middleware('throttle:60,1')->group(function () {
        
    Route::middleware('auth:sanctum')->group(function () {

        Route::controller(teacher::class)->group(function () {
            Route::post('/CreateTeacher', 'CreateTeacher');
            Route::post('/GetTeacher', 'GetTeacher');
            Route::get('/GetTeacherData', 'GetTeacherData');
            Route::post('/UpdateTeacher', 'UpdateTeacher');
            Route::post('/DeleteTeacher', 'Delete');
            Route::post('/GetTeacherInformation', 'GetTeacherInformation');
            Route::get('/GetTeacherClassinfo', 'GetTeacherClassinfo');
            Route::get('/teacherattendance', 'teacherattendance');
            Route::get('/GetTeacherAttendance','GetTeacherAttendance');
            Route::get('/GetTeacherAttendanceDashboard','GetTeacherAttendanceDashboard');
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
            Route::get('/ResetPassword','ResetPassword');
            Route::get('/GetStudentWeekAttendance','GetStudentWeekAttendance');
            Route::get('/MarkEachAttendance','MarkEachAttendance');
            Route::post('/GetTodayattendance','GetTodayattendance');
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
            Route::get('/destroy-Playlist','destroyPlaylist');
            Route::post('/StoreComment','StoreComment');
        });
        Route::controller(ChatController::class)->group(function () {
            Route::post('/PrivateMessage', 'PrivateMessage');
            Route::post('/MessageStoredData', 'MessageStore');
            Route::get('/GetEachStoredMessages', 'GetEachStoredMessages');
        });
        Route::controller(PriceController::class)->group(function () {
            Route::get('/GenerateStudentFee', 'GenerateStudentFee');
            Route::post('/GenerateStudentFeePaid', 'GenerateStudentFeePaid');
            Route::get('/GeneratedPaidFee','GeneratedPaidFee');
            Route::get('/StudentGeneratedFee','StudentGeneratedFee');
            Route::get('/TeacherPayPaid', 'TeacherPayPaid');
            Route::post('/Addexpensives', 'Addexpensives');
            Route::get('/TotalExpensives', 'TotalExpensives');
            Route::get('/GeneratedChallans', 'GeneratedChallans');
        });
        Route::controller(timetable::class)->group(function () {
            Route::post('/CreateTimeTable', 'create');
            Route::get('/GetTimeTable','show');
            Route::get('/destroyTimeTable', 'destroy');
        });
        Route::controller(AnnouncementController::class)->group(function () {
            Route::post('/createAnnouncement', 'create');
            Route::get('/showAnnouncement','show');
            Route::get('/showAllAnnouncement','showAll');
            Route::get('/destroyAnnouncement','destroy');
        });
        Route::get('/user', [AuthController::class, 'User']);
        Route::post('/update-database-name', [EnvController::class, 'updateDatabaseName']);
    });
    
});

    Route::middleware('throttle:15,1')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/login', 'login');
        Route::post('/register', 'register');
        Route::post('/forgotPassword', 'forgotPassword');
    });
    });
    // Route::get('/verify-email', [AuthController::class, 'verifyEmail'])->name('verify.email');
});



Route::post('/SendMessage', [ChatController::class, 'sendMessage']);
Route::get('/csrf-token', function () {
    return response()->json(['csrfToken' => csrf_token()]);
});