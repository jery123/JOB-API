<?php

use App\Http\Controllers\Api\v1\Authcontroller;
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
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
