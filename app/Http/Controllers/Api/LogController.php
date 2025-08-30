<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Log; // Menggunakan model Log sesuai permintaan
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log as FacadesLog;

class LogController extends Controller
{
    /**
     * Get the most recent user activities for the dashboard log.
     *
     * @return JsonResponse
     */
     public function getRecentActivities(): JsonResponse
    {
        try {
            // Fetch the 10 most recent activities, ordered by creation time.
            // Eager load the 'userMission' relationship to get user and mission details.
            $activities = Log::with('userMission.user', 'userMission.mission')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Format the data for a clean response
            $formattedActivities = $activities->map(function ($log) {
                // Access user data through the userMission relationship
                $user = $log->userMission->user;
                
                return [
                    'user_name' => $user->name,
                    'description' => $log->aktivitas_terbaru,
                    'timestamp' => $log->created_at,
                    'time_ago' => $log->created_at,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Recent activities retrieved successfully.',
                'data' => $formattedActivities,
            ]);

        } catch (\Exception $e) {
            // Handle any errors that occur
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving activities.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
