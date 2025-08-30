<?php
// File: app/Models/User.php
// Deskripsi: Model untuk tabel 'user'.

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'user';
    protected $primaryKey = 'id_user';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'username',
        'password',
        'role',
        'total_point',
    ];

    protected $hidden = [
        'password',
    ];

    // Relasi ke tabel 'certificate'
    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'id_user');
    }

    // Relasi ke tabel 'user_mission'
    public function userMissions()
    {
        return $this->hasMany(UserMission::class, 'id_user');
    }

    // Relasi ke tabel 'upload'
    public function uploads()
    {
        return $this->hasMany(Upload::class, 'id_user');
    }
}
