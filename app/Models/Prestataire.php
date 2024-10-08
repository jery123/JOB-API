<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prestataire extends Model
{
    protected $fillable = ['user_id', 'prix', 'competence', 'description', 'portfolio', 'age', 'langue'];

    // Relation avec User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
