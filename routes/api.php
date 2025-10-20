<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MisiController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ApprovalController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\ReportController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//RUTE CRUD & MONITORING KARYAWAN (Publik)
Route::prefix('admin/users')->group(function () {

    // 1. Rute CRUD 
    // R: Read (List Semua Karyawan/Peserta)
    Route::get('/', [UserController::class, 'index']); 
    
    // C: Create (Tambah Karyawan Baru)
    Route::post('/', [UserController::class, 'store']); 

    // R: Read (Detail Karyawan untuk Edit/Hapus)
    Route::get('/{id_user}', [UserController::class, 'showEmployeeDetail']);
    
    // U: Update (Ubah Data Karyawan)
    Route::put('/{id_user}', [UserController::class, 'update']);
    
    // D: Delete (Hapus Karyawan)
    Route::delete('/{id_user}', [UserController::class, 'destroy']);


    // 2. Rute Monitoring/Laporan Spesifik
    // URL: GET /api/admin/users/{id_user}/progress
    Route::get('/{id_user}/progress', [UserController::class, 'showParticipantProgress']);

    // R: Read (Bukti Misi Peserta)
    Route::get('/{id_user}/missions/{id_mission}/proofs', [UserController::class, 'showMissionProofs']);
});

    // Route::get('users', [UserController::class, 'index']);
    // Route::post('/', [UserController::class, 'store']);
    // Route::get('users/{id_user}/progress', [UserController::class, 'showParticipantProgress']);
    // Route::get('users/{id_user}/missions/{id_mission}/proofs', [UserController::class, 'showMissionProofs']);
    
    // Tambahan rute untuk admin terkait sertifikat
    Route::get('users/top-users', [UserController::class, 'getTopUsers']); // Rute baru untuk daftar kandidat sertifikat
    Route::post('users/{id_user}/award-certificate', [UserController::class, 'awardCertificate']); // Rute baru untuk menerbitkan sertifikat

    // Persetujuan bukti oleh Admin
    Route::put('responsible-person/proofs/{id_upload}/update-status', [UploadController::class, 'updateStatus']);

    // RUTE UNTUK LAPORAN (Admin Access)
    Route::prefix('admin/reports')->group(function () {
        
        // 1. Laporan Ringkasan Kinerja Misi (JSON)
        // Contoh akses: /api/admin/reports/mission-performance?start_date=2024-09-01&end_date=2024-09-30
        Route::get('mission-performance', [ReportController::class, 'getMissionPerformanceReport']);

        // 2. Laporan Ringkasan Kinerja Karyawan (JSON)
        // Contoh akses: /api/admin/reports/user-performance?start_date=2024-09-01&end_date=2024-09-30
        Route::get('user-performance', [ReportController::class, 'getUserPerformanceReport']);
        
        // 3. Laporan Detail Aktivitas Per Karyawan (CSV/Excel)
        // Contoh akses: /api/admin/reports/user-detail-export?start_date=2024-09-01&end_date=2024-09-30
        Route::get('user-detail-export', [ReportController::class, 'exportMonthlyUserDetailReport']);
    });

// Rute untuk Penanggung Jawab
Route::prefix('responsible-person')->group(function () {
    // Melihat daftar bukti yang butuh persetujuan
    Route::get('pending-proofs', [ApprovalController::class, 'getPendingProofsForResponsiblePerson']);

    // Persetujuan bukti oleh Penanggung Jawab
    Route::put('proofs/{id_upload}/update-status', [ApprovalController::class, 'updateProofStatus']);
});

// Rute untuk Peserta
Route::post('/upload/store', [UploadController::class, 'store']);
// Tambahan rute untuk peserta melihat sertifikatnya sendiri
Route::get('users/{id_user}/certificates', [UserController::class, 'showUserCertificates']);

