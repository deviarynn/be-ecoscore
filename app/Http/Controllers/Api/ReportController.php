<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function getMissionPerformanceReport(Request $request): JsonResponse
    {
        try {
            $startDate = $request->input('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

            $missions = Mission::all();
            $reportData = [];

            foreach ($missions as $mission) {
                $uploads = Upload::where('id_mission', $mission->id_mission)
                    ->whereBetween('uploaded_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                    ->get();
                
                $totalUploads = $uploads->count();
                $verifiedUploads = $uploads->where('status', 'Terverifikasi')->count();
                $rejectedUploads = $uploads->where('status', 'Ditolak')->count();
                
                $totalPointsGiven = $verifiedUploads * $mission->point;
                
                // successRate dihapus sesuai permintaan
                // $successRate = $totalUploads > 0 ? round(($verifiedUploads / $totalUploads) * 100, 2) : 0;

                $reportData[] = [
                    'id_mission' => $mission->id_mission,
                    'nama_misi' => $mission->title,
                    'penanggung_jawab' => $mission->penanggungjawab,
                    'total_unggahan' => $totalUploads,
                    'terverifikasi' => $verifiedUploads,
                    'ditolak' => $rejectedUploads,
                    'total_poin_diberikan' => $totalPointsGiven,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Laporan kinerja misi berhasil diambil.',
                'periode_laporan' => $startDate . ' sampai ' . $endDate,
                'data' => $reportData,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil laporan kinerja.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getUserPerformanceReport(Request $request): JsonResponse
    {
        try {
            $startDate = $request->input('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

            $users = User::where('role', 'karyawan')->get();
            $reportData = [];

            foreach ($users as $user) {
                $uploads = Upload::where('id_user', $user->id_user)
                    ->whereBetween('uploaded_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                    ->get();

                $totalUploads = $uploads->count();
                $verifiedUploads = $uploads->where('status', 'Terverifikasi')->count();
                $rejectedUploads = $uploads->where('status', 'Ditolak')->count();

                $totalPointsEarnedThisPeriod = 0;
                foreach ($uploads->where('status', 'Terverifikasi') as $upload) {
                    $mission = Mission::find($upload->id_mission);
                    if ($mission) {
                        $totalPointsEarnedThisPeriod += $mission->point;
                    }
                }

                $reportData[] = [
                    'id_user' => $user->id_user,
                    'nama_karyawan' => $user->name,
                    'total_poin_saat_ini' => $user->total_point,
                    'poin_diperoleh_periode_ini' => $totalPointsEarnedThisPeriod,
                    'total_unggahan_periode_ini' => $totalUploads,
                    'unggahan_terverifikasi' => $verifiedUploads,
                    'unggahan_ditolak' => $rejectedUploads,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Laporan kinerja per karyawan berhasil diambil.',
                'periode_laporan' => $startDate . ' sampai ' . $endDate,
                'data' => $reportData,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil laporan kinerja per karyawan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportMonthlyUserDetailReport(Request $request): StreamedResponse
    {
        // Tetapkan tanggal awal dan akhir (default bulan ini)
        $now = Carbon::now();
        $startDate = $request->input('start_date', $now->copy()->startOfMonth()->format('Y-m-d')); // Gunakan copy() agar now tidak berubah
        $endDate = $request->input('end_date', $now->copy()->endOfMonth()->format('Y-m-d'));
        $reportMonthYear = Carbon::parse($startDate)->isoFormat('MMMM YYYY');
        
        $fileName = 'Laporan_Detail_Aktivitas_' . str_replace(' ', '_', $reportMonthYear) . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $data = DB::table('upload')
            ->join('user', 'upload.id_user', '=', 'user.id_user')
            ->join('mission', 'upload.id_mission', '=', 'mission.id_mission')
            ->where('user.role', 'karyawan')
            ->whereBetween('upload.uploaded_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                'user.name as nama_karyawan',
                'user.username',
                'mission.title as judul_misi',
                'mission.point as poin_misi',
                'upload.status as status_unggahan',
                'upload.uploaded_at as tanggal_unggah',
                'upload.verified_at as tanggal_verifikasi',
                'upload.file_path_before',
                'upload.file_path_after'
            )
            ->orderBy('user.name')
            ->orderBy('upload.uploaded_at')
            ->get();

        $callback = function() use ($data, $reportMonthYear) {
            $file = fopen('php://output', 'w');

            fputs($file, "\xEF\xBB\xBF");
            
            $columns = [
                'BULAN_LAPORAN', 
                'NAMA_KARYAWAN', 
                'USERNAME', 
                'JUDUL_MISI', 
                'POIN_MISI', 
                'STATUS_UNGGAHAN', 
                'TANGGAL_UNGGAH', 
                'TANGGAL_VERIFIKASI',
                'LINK_BUKTI_SEBELUM',
                'LINK_BUKTI_SESUDAH'
            ];
            fputcsv($file, $columns, ';');

            foreach ($data as $row) {
                fputcsv($file, [
                    $reportMonthYear,
                    $row->nama_karyawan,
                    $row->username,
                    $row->judul_misi,
                    $row->poin_misi,
                    $row->status_unggahan,
                    $row->tanggal_unggah ? Carbon::parse($row->tanggal_unggah)->isoFormat('D MMMM YYYY, HH:mm') : '-',
                    $row->tanggal_verifikasi ? Carbon::parse($row->tanggal_verifikasi)->isoFormat('D MMMM YYYY, HH:mm') : '-',
                    $row->file_path_before, 
                    $row->file_path_after,
                ], ';');
            }

            fclose($file);
        };

        return response()->streamDownload($callback, $fileName, $headers);
    }
}
