<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    use HasFactory;
    
    protected $table = 'certificate';
    protected $primaryKey = 'id_certificate';
    public $timestamps = false; // Tidak ada timestamps di migrasi

    protected $fillable = [
        'id_user',
        'certificate_name',
        'file_path',
        'issued_date',
    ];

    /**
     * Relasi: Certificate dimiliki oleh seorang User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
