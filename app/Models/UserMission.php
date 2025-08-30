<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMission extends Model
{
    use HasFactory;

    protected $table = 'user_mission';
    protected $primaryKey = 'id_user_mission';
    public $timestamps = false; // Karena migrasi tidak menggunakan timestamps()

    protected $fillable = [
        'id_user',
        'id_mission',
        'submitted_at',
        'verified_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Relasi: user_mission dimiliki oleh seorang User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    /**
     * Relasi: user_mission dimiliki oleh sebuah Mission.
     */
    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class, 'id_mission', 'id_mission');
    }
}
