<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Upload;
use App\Models\User;
use App\Models\Mission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    /**
     * Menampilkan daftar berkas yang "Menunggu Verifikasi" untuk penanggung jawab.
     * @param Request $request
     * @return JsonResponse
     */
    public function getPendingProofsForResponsiblePerson(Request $request): JsonResponse
    {
        try {
            //CATATAN: KODE INI SEMENTARA DIKOMENTARI UNTUK TUJUAN PENGUJIAN API TANPA OTENTIKASI.
            // if (!$request->user()) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Akses ditolak. Pengguna tidak terautentikasi.'
            //     ], 401);
            // }
            
            // Mengambil semua unggahan yang berstatus 'Menunggu Verifikasi'
            // dengan memuat data user dan misi terkait untuk ditampilkan.
            $pendingUploads = Upload::where('status', 'Menunggu Verifikasi')
                ->with(['user', 'mission'])
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Daftar bukti menunggu verifikasi berhasil diambil.',
                'data' => $pendingUploads
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil daftar bukti.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Memperbarui status unggahan bukti (disetujui/ditolak).
     * @param Request $request
     * @param int $id_upload
     * @return JsonResponse
     */
    public function updateProofStatus(Request $request, int $id_upload): JsonResponse
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
