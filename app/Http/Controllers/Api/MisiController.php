<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MisiController extends Controller
{
    // API untuk Admin (CRUD Misi)

    /**
     * Menampilkan daftar semua misi.
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $missions = Mission::with('responsiblePerson')->get();
            return response()->json([
                'success' => true,
                'message' => 'Daftar misi berhasil diambil.',
                'data' => $missions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil misi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyimpan misi baru.
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:100',
            'deskripsi' => 'required|string',
            'point' => 'required|integer|min:0',
            'start' => 'required|date_format:H:i:s',
            'end' => 'required|date_format:H:i:s|after:start',
            'responsible_user_id' => 'required|integer|exists:user,id_user',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $mission = Mission::create($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Misi berhasil dibuat.',
                'data' => $mission
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat misi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan detail misi.
     * @param int $id_mission
     * @return JsonResponse
     */
    public function show(int $id_mission): JsonResponse
    {
        try {
            $mission = Mission::with('responsiblePerson')->find($id_mission);

            if (!$mission) {
                return response()->json(['success' => false, 'message' => 'Misi tidak ditemukan.'], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Misi berhasil ditemukan.',
                'data' => $mission
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil detail misi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Memperbarui detail misi.
     * @param Request $request
     * @param int $id_mission
     * @return JsonResponse
     */
    public function update(Request $request, int $id_mission): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'string|max:100',
            'deskripsi' => 'string',
            'point' => 'integer|min:0',
            'start' => 'date_format:H:i:s',
            'end' => 'date_format:H:i:s|after:start',
            'responsible_user_id' => 'integer|exists:user,id_user',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $mission = Mission::find($id_mission);
            if (!$mission) {
                return response()->json(['success' => false, 'message' => 'Misi tidak ditemukan.'], 404);
            }
            $mission->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Misi berhasil diperbarui.',
                'data' => $mission
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui misi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menghapus misi.
     * @param int $id_mission
     * @return JsonResponse
     */
    public function destroy(int $id_mission): JsonResponse
    {
        try {
            $mission = Mission::find($id_mission);
            if (!$mission) {
                return response()->json(['success' => false, 'message' => 'Misi tidak ditemukan.'], 404);
            }
            $mission->delete();

            return response()->json([
                'success' => true,
                'message' => 'Misi berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus misi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
