<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Log extends Model
{
    use HasFactory;
    
    protected $table = 'log';
    protected $primaryKey = 'id_log';
    public $timestamps = false;

    protected $fillable = [
        'id_user_mission',
        'aktivitas_terbaru',
    ];
}
