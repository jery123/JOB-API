<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->nullable();
            $table->string('prenom')->nullable();
            $table->string('ville')->nullable();
            $table->string('photo')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role'); // 'client', 'prestataire', 'admin'
            $table->string('otp')->nullable();  // OTP pour vérification
            $table->timestamp('otp_expires_at')->nullable(); // Date d'expiration de l'OTP
            $table->timestamps();
            $table->timestamp('email_verified_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
