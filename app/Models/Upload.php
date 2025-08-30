<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Upload extends Model
{
    use HasFactory;

    protected $table = 'upload';
    protected $primaryKey = 'id_upload';
    public $timestamps = false; // Karena migrasi tidak menggunakan timestamps()

    protected $fillable = [
        'id_user',
        'id_mission',
        'file_path',
        'status',
        'uploaded_at',
        'verified_at',
    ];

    /**
     * Relasi: Upload dimiliki oleh seorang User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    /**
     * Relasi: Upload dimiliki oleh sebuah Mission.
     */
    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class, 'id_mission', 'id_mission');
    }
}
