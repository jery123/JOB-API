<?php

use App\Http\Controllers\Api\v1\Authcontroller;
use App\Http\Controllers\Api\v1\ProfilController;
use App\Http\Controllers\Api\v1\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('register', [Authcontroller::class, 'register']);
Route::post('login', [Authcontroller::class, 'login']);
Route::post('verify-mail', [Authcontroller::class, 'verifyEmail']);
Route::post('resend-otp', [Authcontroller::class, 'rensendOtp']);

Route::post('/forgot-password', [Authcontroller::class, 'forgotPassword']);
Route::post('/verify-otp', [Authcontroller::class, 'verifyOtp']);
Route::post('/reset-password', [Authcontroller::class, 'resetPassword']);

// user
Route::prefix('user')->group(function(){
    Route::post('profile', [UserController::class, 'profile']); //to get the user profile
    Route::post('edit', [UserController::class, 'updateProfile']); //to edit the user profile
    Route::post('delete', [UserController::class, 'deleteProfile']); //to delete the user profile
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

