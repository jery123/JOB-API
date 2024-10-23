<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller {

    // get the authenticated user method
    /**
    * params:
    * -id = 1, 2, 3, ...................
    */

    public function profile( Request $request ) {
        try {

            $request->validate( [
                'id' => 'required'
            ] );

            $user = User::find( $request->id );
            if ( !$user ) {
                return response()->json( [ 'status' => false, 'message' => 'User not found.' ], 404 );
            }

            $userData = [
                'id' => $user->id,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'ville' => $user->ville,
                'photo' => $user->photo,
                'email' => $user->email,
                'role' => $user->role,
                'otp' => $user->otp,
                'otp_expires_at' => $user->otp_expires_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'email_verified_at' => $user->email_verified_at,
            ];

            if ( $user->role === 'prestataire' ) {
                $prestataire = $user->prestataire;
                if ( $prestataire ) {
                    $userData[ 'prix' ] = $prestataire->prix ?? '';
                    $userData[ 'competence' ] = $prestataire->competence ?? '';
                    $userData[ 'description' ] = $prestataire->description ?? '';
                    $userData[ 'portfolio' ] = $prestataire->portfolio ?? '';
                    $userData[ 'age' ] = $prestataire->age ?? '';
                    $userData[ 'langue' ] = $prestataire->langue ?? '';
                }
            }

            return response()->json( [
                'status' => true,
                'message' => 'Profile d\'utilisateur.',
                'user_data' => $userData,
            ], 200);


        } catch ( \Exception $th ) {
            return response()->json( [ 'status' => false, 'error' => $th->getMessage() ], 500 );
        }
    }

    public function deleteProfile( Request $request ) {
        try {

            $request->validate( [
                'id' => 'required'
            ] );
            
            $user = User::find( $request->id );
            if ( !$user ) {
                return response()->json( [ 'status' => false, 'message' => 'User not found.' ], 404 );
            }
            if($user->role == 'client'){
                $user->client->delete();
            }
            if($user->role == 'prestataire'){
                $user->prestataire->delete();
            }

            $user->delete();

            return response()->json( [
                'status' => true,
                'message' => 'Profile supprimé.',
            ], 200);

        } catch ( \Exception $th ) {
            return response()->json( [ 'status' => false, 'error' => $th->getMessage() ], 500 );
        }
    }


    /**
    * Edit profil
    * params:
    * -user_id = 1, 2, 3, .........................
    * -
    */

    public function updateProfile( Request $request ) {
        try {
            $request->validate( [
                'user_id' => 'required',
                'nom' => 'required',
                'prenom' => 'required',
                'ville' => 'required',
                // 'photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ] );
            
            
            if ( $request->hasfile( 'photo' ) ) {
                $imageFile = $request->file( 'photo' );
                $name = str_replace( ' ', '_', time().'_'.$imageFile->getClientOriginalName() );
                $imageFile->move( public_path().'/profile/', $name );
                $documentfile = '/profile/'.$name;
            }

            $user = User::find( $request->id );
            if ( !$user ) {
                return response()->json( [ 'status' => false, 'message' => 'User not found.' ], 404 );
            }
            $user->nom = $request->nom;
            $user->prenom = $request->prenom;
            $user->ville = $request->nom;
            $user->photo = $documentfile ?? null;
            
            if($user->role == 'prestataire'){
                $request->validate([
                    'prix'=>"required",
                    'competence'=>"required",
                    'description'=>"required",
                    'description'=>"required",
                    'age'=>"required",
                    'langue'=>"required",
                ]);
                if ( $request->hasfile( 'portfolio' ) ) {
                    $imageFile = $request->file( 'portfolio' );
                    $name = str_replace( ' ', '_', time().'_'.$imageFile->getClientOriginalName() );
                    $imageFile->move( public_path().'/portfolio/', $name );
                    $documentFile = '/portfolio/'.$name;
                }
                $pres_data = $user->prestataire;
                $pres_data->prix = $request->prix;
                $pres_data->competence = $request->competence;
                $pres_data->description = $request->description;
                $pres_data->portfolio = $documentFile ?? '';
                $pres_data->age = $request->age;
                $pres_data->langue = $request->langue;
                $pres_data->save();
            }

            $user->save();

            return response()->json( [
                'status' => true,
                'message' => 'Profile edited.',
            ], 200);

        } catch ( \Exception $th ) {
            return response()->json( [ 'status' => false, 'error' => $th->getMessage() ], 500 );
            }
        }
    }
