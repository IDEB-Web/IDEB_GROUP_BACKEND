<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordCustom; 

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
    'name', 'email', 'password', 'google_id', 'role', 'status',
    'telefono', 'zonaHoraria', 'idioma', 'foto'
];


    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function sendPasswordResetNotification($token)
    {
        $frontend = config('app.frontend_url', 'http://localhost:4200');
        $url = $frontend . '/auth/reset-password?token=' . $token;
        $this->notify(new ResetPasswordCustom($url));
    }
}






