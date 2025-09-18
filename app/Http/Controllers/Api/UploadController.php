<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\Upload;
use App\Models\User;
use App\Models\UserMission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;

class UploadController extends Controller
{
    // API untuk Peserta (mengunggah berkas)

    /**
     * Menyimpan unggahan berkas baru.
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|integer|exists:user,id_user',
            'id_mission' => 'required|integer|exists:mission,id_mission',
            'file_path_before' => 'required|string',
            'file_path_after' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // 1. Ambil detail misi dan periksa batasan waktu
            $mission = Mission::find($request->id_mission);
            if (!$mission) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Misi tidak ditemukan.'], 404);
            }

            // Dapatkan waktu saat ini di zona waktu yang diinginkan (contoh: Asia/Jakarta)
            $now = Carbon::now('Asia/Jakarta');

            // Periksa jika ada batasan waktu
            if ($mission->start && $mission->end) {
                // Buat objek Carbon dengan zona waktu yang sama untuk perbandingan yang akurat
                $startTime = Carbon::createFromFormat('H:i:s', $mission->start, 'Asia/Jakarta')->setDate($now->year, $now->month, $now->day);
                $endTime = Carbon::createFromFormat('H:i:s', $mission->end, 'Asia/Jakarta')->setDate($now->year, $now->month, $now->day);
                
                // Tambahkan satu hari jika waktu akhir kurang dari waktu mulai (misalnya dari jam 22:00 sampai 02:00)
                if ($endTime->lt($startTime)) {
                    $endTime->addDay();
                }

                if (!$now->between($startTime, $endTime)) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Unggahan hanya diperbolehkan antara ' . $mission->start . ' dan ' . $mission->end
                    ], 403);
                }
            }

            // Cek apakah sudah ada unggahan untuk user & misi ini
            $existingUpload = Upload::where('id_user', $request->id_user)
                ->where('id_mission', $request->id_mission)
                ->first();

            if ($existingUpload) {
                // Jika sudah ada, perbarui unggahan
                $existingUpload->file_path_before = $request->file_path_before;
                $existingUpload->file_path_after = $request->file_path_after;
                $existingUpload->status = 'Menunggu Verifikasi';
                $existingUpload->uploaded_at = now();
                $existingUpload->verified_at = null;
                $existingUpload->save();
                $upload = $existingUpload;
            } else {
                // Jika belum ada, buat unggahan baru
                $upload = Upload::create([
                    'id_user' => $request->id_user,
                    'id_mission' => $request->id_mission,
                    'file_path_before' => $request->file_path_before,
                    'file_path_after' => $request->file_path_after,
                    'status' => 'Menunggu Verifikasi',
                    'uploaded_at' => now(),
                ]);
            }

            // Catat ke user_mission
            $userMission = UserMission::firstOrCreate(
                ['id_user' => $request->id_user, 'id_mission' => $request->id_mission],
                ['submitted_at' => now()]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berkas berhasil diunggah dan menunggu verifikasi.',
                'data' => $upload
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengunggah berkas.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, int $id_upload): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Terverifikasi,Ditolak,Menunggu Verifikasi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Status tidak valid. Gunakan "Terverifikasi", "Ditolak", atau "Menunggu Verifikasi".',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            DB::beginTransaction();

            $upload = Upload::find($id_upload);
            if (!$upload) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Bukti tidak ditemukan.'], 404);
            }

            $oldStatus = $upload->status;
            $newStatus = $request->status;

            // Perbarui status unggahan
            $upload->status = $newStatus;
            $upload->verified_at = now();
            $upload->save();

            // Logika penambahan/pengurangan poin
            if ($newStatus === 'Terverifikasi' && $oldStatus !== 'Terverifikasi') {
                $mission = Mission::find($upload->id_mission);
                $user = User::find($upload->id_user);

                if ($mission && $user) {
                    $user->total_point += $mission->point;
                    $user->save();
                }
            } else if ($newStatus !== 'Terverifikasi' && $oldStatus === 'Terverifikasi') {
                $mission = Mission::find($upload->id_mission);
                $user = User::find($upload->id_user);

                if ($mission && $user) {
                    $user->total_point -= $mission->point;
                    $user->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status bukti berhasil diperbarui menjadi ' . $request->status,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui status bukti.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
