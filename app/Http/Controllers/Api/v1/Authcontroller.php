<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Mail\V1\sendOtp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class Authcontroller extends Controller
{
    /**
     * registration function
     */
    public function register(Request $request)
    {
        try {

            $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6|confirmed',
            ]);

            // Generate a 5-digit OTP
            $otp = strval(random_int(10000, 99999));

            // Set expiration time (24 hours from now) using Carbon
            $expiration = Carbon::now()->addHours(24);
         
            $user = User::create(
                [
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                    'otp'=>$otp,
                    'otp_expires_at'=>$expiration
                ]
            );
   
            //send the email to verify the email address
            Mail::to($user->email)->send(new sendOtp($otp));

            return response()->json([
                'status'=>true,
                'message'=>"User register with success",
                'user_date'=>$user
            ], 200);

        } catch (\Exception $th) {
            return response()->json(['status' => false, 'error' => $th->getMessage()], 500);
        }
    }
}
