<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Mail\V1\sendOtp;
use App\Models\User;
use App\Models\Client;
use App\Models\Prestataire;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Mail\WelcomeEmail ;
use Illuminate\Support\Facades\Hash;


class Authcontroller extends Controller
{
    /**
     * registration function
     */
    public function register(Request $request)
    {
        try {
            // Validation des données d'entrée
            $request->validate([
                'name' => 'required|string|max:255', // Ajout de la validation pour le nom
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6',
                'role' => ['required', Rule::in(['client', 'prestataire', 'admin'])]
            ]);

            // Générer un OTP de 5 chiffres
            $otp = strval(random_int(10000, 99999));

            // Définir l'expiration de l'OTP (24 heures)
            $expiration = Carbon::now()->addHours(24);

            // Créer l'utilisateur dans la table `users`
            $user = User::create([
                'nom' => $request->name, // Ajout du nom
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role' => $request->role,
                'otp' => $otp,
                'otp_expires_at' => $expiration
            ]);

            // Inscrire l'utilisateur dans la table `clients` ou `prestataires`
            if ($request->role == 'client') {
                Client::create([
                    'user_id' => $user->id,
                ]);
            } elseif ($request->role == 'prestataire') {
                Prestataire::create([
                    'user_id' => $user->id,
                    'prix' => null,
                    'competence' => null,
                    'description' => null,
                    'portfolio' => null,
                    'age' => null,
                    'langue' => null,
                ]);
            }

            // Envoi de l'OTP par email pour vérification
            Mail::to($user->email)->send(new sendOtp($otp));

            // Envoi de l'email de bienvenue
            Mail::to($user->email)->send(new WelcomeEmail($user));

            return response()->json([
                'status' => true,
                'message' => "User registered successfully. Please verify your email with the OTP sent.",
                'user_data' => $user
            ], 200);

        } catch (\Exception $th) {
            return response()->json(['status' => false, 'error' => $th->getMessage()], 500);
        }
    }


    public function login(Request $request)
    {
        try {
            // Valider les données d'entrée
            $request->validate([
                'email' => 'required',
                'password' => 'required',
            ]);

            // Récupérer l'utilisateur correspondant à l'email fourni
            $user = User::where('email', $request->email)->first();

            // Vérifier si le compte utilisateur est activé
            if (!$user || !$user->email_verified_at) {
                return response()->json(['status' => false, 'message' => "Votre compte n'est pas encore activé."], 422);
            }

            // Vérifier les informations d'identification (email et mot de passe)
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Les informations d\'identification sont incorrectes.',
                ], 422);
            }

            // Authentifier l'utilisateur et démarrer une session
            auth()->login($user);

            return response()->json([
                'status' => true,
                'message' => 'Connexion réussie. La session a été démarrée.',
                'user_data' => $user,
            ], 200);

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
// logout a user method

    public function logout( Request $request ) {
        $request->user()->currentAccessToken()->delete();

        $cookie = cookie()->forget( 'token' );

        return response()->json( [
            'message' => 'Logged out successfully!'
        ] )->withCookie( $cookie );
    }


}
