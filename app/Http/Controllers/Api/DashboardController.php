<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserMission;
use App\Models\Mission;
use App\Models\Log;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Get aggregated data for the admin dashboard.
     * This method does not require a user ID as it shows global statistics.
     *
     * @return JsonResponse
     */
    public function getJumlahPohon(): JsonResponse
    {
        try {
            // Menghitung total pohon yang ditanam oleh semua user.
            // Misi dianggap selesai ketika progress sama dengan target.
            // Nama tabel 'mission' (tunggal) digunakan sesuai skema database yang dikonfirmasi.
            $totalPohon = UserMission::join('mission', 'user_mission.id_mission', '=', 'mission.id_mission')
                ->whereColumn('user_mission.progress', '=', 'mission.target')
                ->sum('user_mission.progress');
            
            return response()->json([
                'success' => true,
                'message' => 'Total pohon tertanam berhasil diambil.',
                'data' => [
                    'total_pohon_tertanam' => $totalPohon,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil total pohon tertanam.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // public function getLeaderboardData(): JsonResponse
    // {
    //     try {
    //         // Mengambil 10 pengguna teratas berdasarkan total poin.
    //         $leaderboard = User::orderBy('total_point', 'desc')
    //             ->select('name', 'total_point')
    //             ->limit(10)
    //             ->get();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Leaderboard data retrieved successfully.',
    //             'data' => $leaderboard,
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'An error occurred while fetching leaderboard data.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function getSkorTertinggi(): JsonResponse
    {
        try {
            // Mengambil satu pengguna dengan total poin tertinggi.
            $leaderboard = User::orderBy('total_point', 'desc')
                ->select('name', 'total_point')
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Leaderboard data retrieved successfully.',
                'data' => $leaderboard,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching leaderboard data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
