<?php

use App\Http\Controllers\Api\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MailController;
use App\Http\Controllers\Api\ChatRoomController;


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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('login', [UserController::class, 'login']);
Route::post('register', [UserController::class, 'register']);
Route::post('logout', [UserController::class, 'logout'])->middleware('auth:api');

Route::group(['middleware' => 'auth:api'], function () {

    Route::post('user-details', [UserController::class, 'userDetails']);
    Route::post('email/verification-notification', [UserController::class, 'sendVerificationEmail']);
    Route::get('verify-email/{id}/{hash}', [UserController::class, 'verify'])->name('verification.verify');

    Route::post('forgot-password', [UserController::class, 'forgotPassword']);
    Route::post('reset-password', [UserController::class, 'reset']);

    Route::post('verify_otp', [UserController::class, 'verifyOtp']);
    Route::any('sendOtp',  [UserController::class,'sendOtp']);

    Route::post('sendEmail', [MailController::class, 'sendEmail']);
    Route::post('sendEmailVerification', [MailController::class, 'sendEmailVerification']);


    Route::controller(ChatRoomController::class)->group(function(){
        Route::group(['prefix' => 'chat'], function(){
            Route::get('chat-room', 'index');
            Route::post('add-chat-room', 'create');
            Route::post('add-user-in-chat-room', 'addUserInChatRoom');
            Route::post('send-user-msg-in-chat-room', 'sendUserMsgInChatRoom');


        });
    });


    //  Route::any('request_otp', [UserController::class, 'requestOtp']);

});
