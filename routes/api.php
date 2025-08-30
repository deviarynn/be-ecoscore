<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MisiController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ApprovalController;
use App\Http\Controllers\Api\UploadController;

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

// Rute untuk Admin
Route::prefix('admin')->group(function () {
    // CRUD Misi menggunakan Route::apiResource
    Route::apiResource('missions', MisiController::class); // Ini akan mendaftarkan 5 rute CRUD
    
    // Melihat daftar peserta dan progresnya
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{id_user}/progress', [UserController::class, 'showParticipantProgress']);
    Route::get('users/{id_user}/missions/{id_mission}/proofs', [UserController::class, 'showMissionProofs']);
    
    // Tambahan rute untuk admin terkait sertifikat
    Route::get('users/top-users', [UserController::class, 'getTopUsers']); // Rute baru untuk daftar kandidat sertifikat
    Route::post('users/{id_user}/award-certificate', [UserController::class, 'awardCertificate']); // Rute baru untuk menerbitkan sertifikat

    // Persetujuan bukti oleh Admin
    Route::put('responsible-person/proofs/{id_upload}/update-status', [UploadController::class, 'updateStatus']);});

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

// Catatan: Anda perlu menambahkan rute otentikasi (login) dan middleware untuk melindungi rute di atas.
// Contoh: Route::middleware('auth:api')->prefix('admin')->group(function () { ... });
