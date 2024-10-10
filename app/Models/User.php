<?php
namespace App\Models;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'nom', 'prenom', 'ville', 'photo', 'email', 'password', 'role', 'otp', 'otp_expires_at','email_verified_at'
    ];

    protected $hidden = [
        'password', 'remember_token'
    ];

    // Relation avec Client et Prestataire
    public function client()
    {
        return $this->hasOne(Client::class);
    }

    public function prestataire()
    {
        return $this->hasOne(Prestataire::class);
    }
    
}
