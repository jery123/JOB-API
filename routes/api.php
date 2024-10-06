<?php

use App\Http\Controllers\Api\v1\Authcontroller;
use App\Http\Controllers\Api\v1\ProfilController;
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


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
