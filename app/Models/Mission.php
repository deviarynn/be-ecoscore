<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mission extends Model
{
    use HasFactory;

    protected $table = 'mission';
    protected $primaryKey = 'id_mission';
    public $timestamps = false; // Karena migrasi tidak menggunakan timestamps()

    protected $fillable = [
        'title',
        'deskripsi',
        'point',
        'start',
        'end',
        'responsible_user_id',
        'created_at',
    ];

    /**
     * Relasi: Mission memiliki banyak user_mission.
     */
    public function userMissions(): HasMany
    {
        return $this->hasMany(UserMission::class, 'id_mission', 'id_mission');
    }

    /**
     * Relasi: Mission memiliki banyak upload.
     */
    public function uploads(): HasMany
    {
        return $this->hasMany(Upload::class, 'id_mission', 'id_mission');
    }
    
    /**
     * Relasi: Mission dimiliki oleh seorang penanggung jawab.
     */
    public function responsiblePerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id', 'id_user');
    }
}
