<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Mail\V1\sendOtp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
                'role' => [
                        'required', 
                        Rule::in(['client', 'prestataire', 'admin'])
                    ]
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
                    'role' => $request->role,
                    'otp' => $otp,
                    'otp_expires_at' => $expiration
                ]
            );

            //send the email to verify the email address
            Mail::to($user->email)->send(new sendOtp($otp));

            return response()->json([
                'status' => true,
                'message' => "User register with success",
                'user_date' => $user
            ], 200);
        } catch (\Exception $th) {
            return response()->json(['status' => false, 'error' => $th->getMessage()], 500);
        }
    }

    // login
    public function login(Request $request)
    {
        try {

            $request->validate([
                'email' => 'required',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();
            // check if the the account is activated
            if (!$user->email_verified_at) {
                return response()->json(['status' => false, 'message' => "Your account is not activated yet."], 422);
            }

            $credentials = request(['email', 'password']);
            if (!auth()->attempt($credentials)) {
                return response()->json([
                    'message' => "The given data was invalid",
                    'errors' => [
                        'password' => [
                            'Invalid credentials'
                        ]
                    ]
                ], 422);
            }
            $authToken = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'status'=>true,
                'message'=>"Login successfully",
                'access_token'=>$authToken
            ]);
        } catch (\Exception $th) {
            return response()->json(['status' => false, 'error' => $th->getMessage()], 500);
        }
    }

    // Verify email
    public function verifyEmail(Request $request){
        try {
            $request->validate([
                'email'=>"required",
                'otp'=>"required"
            ]);

            $user = User::where('email', $request->email)->first();

            if(!$user){
                return response()->json(['status'=>false, 'message'=>"User account not found."], 404);
            }else if($user->email_verified_at){
                return response()->json(['status'=>false, 'message'=>"Email already verified."], 422);
            }

            if($request->otp != $user->otp){
                return response()->json(['status'=>false, 'message'=>"Invalid otp"], 422);
            }
            // check if the token is not yet expired
            $current_date_time = Carbon::now();
            $expire_otp_time = Carbon::parse($user->otp_expires_at);
            if($current_date_time->greaterThan($expire_otp_time)){
                return response()->json(['status'=>false, 'message'=>"Otp expire."], 422);
            }

            $user->email_verified_at = $current_date_time;
            $user->save();

            return response()->json([
                'status'=>true,
                'message'=>"Email verify successfully."
            ]);
        } catch (\Exception $th) {
            return response()->json(['status' => false, 'error' => $th->getMessage()], 500);
        }
    }

    //resend otp
    public function rensendOtp(Request $request){
        try {
            $request->validate([
                'email'=>"required",
            ]);

            $user = User::where('email', $request->email)->first();

            if(!$user){
                return response()->json(['status'=>false, 'message'=>"User account not found."], 404);
            }else if($user->email_verified_at){
                return response()->json(['status'=>false, 'message'=>"Email already verified."], 422);
            }

            // Generate a 5-digit OTP
            $otp = strval(random_int(10000, 99999));

            // Set expiration time (24 hours from now) using Carbon
            $expiration = Carbon::now()->addHours(24);

            //send the email to verify the email address
            Mail::to($user->email)->send(new sendOtp($otp));

            $user->otp = $otp;
            $user->otp_expires_at = $expiration;
            $user->email_verified_at = null;
            $user->save();

            return response()->json([
                'status'=>true,
                'message'=>"Otp send successfully."
            ]);
        } catch (\Exception $th) {
            return response()->json(['status' => false, 'error' => $th->getMessage()], 500);
        }
    }



// Forgot Password - Generate OTP and send email
public function forgotPassword(Request $request)
{
    try {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['status' => false, 'message' => "User not found."], 404);
        }

        // Generate a 5-digit OTP
        $otp = strval(random_int(10000, 99999));

        // Set expiration time (1 hour from now)
        $expiration = Carbon::now()->addHour(1);

        // Send the email with OTP for resetting password
        Mail::to($user->email)->send(new sendOtp($otp)); // Ensure that sendOtp accepts OTP for reset

        // Save OTP and expiration date in the database
        $user->otp = $otp;
        $user->otp_expires_at = $expiration;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => "OTP sent to your email for password reset."
        ]);
    } catch (\Exception $th) {
        return response()->json(['status' => false, 'error' => $th->getMessage()], 500);
    }
}
// Reset Password

// Verify OTP
public function verifyOtp(Request $request)
{
    try {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required',
        ]);

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['status' => false, 'message' => "User not found."], 404);
        }

        // Check if the OTP matches
        if ($user->otp != $request->otp) {
            return response()->json(['status' => false, 'message' => "Invalid OTP."], 422);
        }

        // Check if OTP has expired
        $current_date_time = Carbon::now();
        if ($current_date_time->greaterThan($user->otp_expires_at)) {
            return response()->json(['status' => false, 'message' => "OTP has expired."], 422);
        }

        // OTP is valid
        return response()->json([
            'status' => true,
            'message' => "OTP is valid, you can now reset your password."
        ]);
    } catch (\Exception $th) {
        return response()->json(['status' => false, 'error' => $th->getMessage()], 500);
    }
}
// Reset Password after OTP is validated
public function resetPassword(Request $request)
{
    try {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',  // Ensure the password confirmation matches
        ]);

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['status' => false, 'message' => "User not found."], 404);
        }

        // Update the password only after OTP is validated
        $user->password = bcrypt($request->password);
        $user->otp = null; // Clear OTP after password reset
        $user->otp_expires_at = null; // Clear OTP expiration time
        $user->save();

        return response()->json([
            'status' => true,
            'message' => "Password reset successfully."
        ]);
    } catch (\Exception $th) {
        return response()->json(['status' => false, 'error' => $th->getMessage()], 500);
    }
}


}
