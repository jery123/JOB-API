<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
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

            $user = User::create(
                [
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                ]
            );

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
