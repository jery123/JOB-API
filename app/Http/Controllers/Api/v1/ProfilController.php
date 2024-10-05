<?php
namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use  App\Models\User;

class ProfilController extends Controller
{
    // Modifier le mot de passe de l'utilisateur connecté
    public function updatePassword(Request $request)
    {
        try {
            // Validation des données
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:6',
            ]);

            // Récupérer l'utilisateur connecté
            $user = auth()->user();

            // Vérifier que l'utilisateur est bien une instance de User
            if (!$user instanceof \App\Models\User) {
                return response()->json(['status' => false, 'message' => "Utilisateur non valide."], 400);
            }

            // Vérifier si le mot de passe actuel est correct
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['status' => false, 'message' => "Le mot de passe actuel est incorrect."], 422);
            }

            // Mettre à jour le mot de passe
            $user->password = Hash::make($request->new_password);

            // Sauvegarder l'utilisateur
            if ($user->save()) {
                return response()->json([
                    'status' => true,
                    'message' => "Mot de passe mis à jour avec succès."
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Erreur lors de la mise à jour du mot de passe."
                ], 500);
            }

        } catch (\Exception $e) {
            // Capturer et afficher l'erreur
            return response()->json([
                'status' => false,
                'message' => "Une erreur est survenue lors de la mise à jour du mot de passe.",
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }



}
