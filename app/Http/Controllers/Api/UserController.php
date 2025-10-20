<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Mission;
use App\Models\Upload;
use App\Models\Certificate; // Pastikan model Certificate sudah di-import
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash; // Tambahkan untuk hashing password
use Illuminate\Validation\Rule;      // Tambahkan untuk validasi 'unique' saat update


class UserController extends Controller
{
    // API untuk Admin (Melihat Peserta)
    
    /**
     * Menampilkan daftar semua peserta dan total poin mereka.
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $users = User::where('role', 'karyawan')
                ->select('id_user', 'name', 'total_point')
                ->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Daftar peserta berhasil diambil.',
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil daftar peserta.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:user,username',
            'email' => 'nullable|email|max:255|unique:user,email', // Email tidak wajib, tapi harus unik
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // 2. Buat Data Karyawan
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                // Email hanya dimasukkan jika ada di request
                'email' => $request->email, 
                'password' => Hash::make($request->password),
                'role' => 'karyawan', // Otomatis diset sebagai karyawan
                'total_point' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Karyawan baru berhasil ditambahkan.',
                'data' => $user->only('id_user', 'name', 'username', 'email', 'role')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data karyawan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tampilkan detail satu karyawan untuk keperluan CRUD (Read One).
     * GET /api/admin/users/{id_user}
     */
    public function showEmployeeDetail($id_user): JsonResponse
    {
        $user = User::where('id_user', $id_user)
            ->where('role', 'karyawan')
            ->select('id_user', 'name', 'username', 'email', 'total_point', 'created_at', 'updated_at')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Perbarui (Update) data karyawan.
     * PUT/PATCH /api/admin/users/{id_user}
     */
    public function update(Request $request, $id_user): JsonResponse
    {
        $user = User::where('id_user', $id_user)
            ->where('role', 'karyawan')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan.'
            ], 404);
        }

        // 1. Validasi Input Update
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:100',
            // Username harus unik, kecuali untuk dirinya sendiri
            'username' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('user', 'username')->ignore($user->id_user, 'id_user'),
            ],
            // Email harus unik, kecuali untuk dirinya sendiri
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('user', 'email')->ignore($user->id_user, 'id_user'),
            ],
            'password' => 'nullable|string|min:8|confirmed', // Password opsional
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // 2. Perbarui Data
            $data = $request->only(['name', 'username', 'email']);

            // Jika password diisi, hash dan tambahkan ke data update
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }
            
            // Pastikan role tidak pernah berubah dari 'karyawan'
            $data['role'] = 'karyawan'; 

            $user->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Data karyawan berhasil diperbarui.',
                'data' => $user->only('id_user', 'name', 'username', 'email', 'role')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data karyawan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus (Delete) karyawan.
     * DELETE /api/admin/users/{id_user}
     */
    public function destroy($id_user): JsonResponse
    {
        // Temukan karyawan dengan role 'karyawan'
        $user = User::where('id_user', $id_user)
            ->where('role', 'karyawan')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan.'
            ], 404);
        }

        try {
            // Hapus pengguna (dan relasinya jika menggunakan cascade delete di database)
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            // Error handling jika ada relasi yang menghalangi penghapusan
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data karyawan. Pastikan tidak ada data terkait (seperti unggahan) yang harus dihapus terlebih dahulu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function showParticipantProgress(int $id_user): JsonResponse
    {
        try {
            $user = User::where('role', 'karyawan')->find($id_user);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Peserta tidak ditemukan.'], 404);
            }

            // Mengambil semua misi yang tersedia di sistem
            $missions = Mission::select('id_mission', 'title', 'point')->get();

            return response()->json([
                'success' => true,
                'message' => 'Progres peserta berhasil diambil.',
                'data' => [
                    'user' => $user->only('id_user', 'name', 'total_point'),
                    'missions' => $missions
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil progres peserta.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan riwayat unggahan bukti untuk misi spesifik dari seorang peserta.
     * @param int $id_user
     * @param int $id_mission
     * @return JsonResponse
     */
    public function showMissionProofs(int $id_user, int $id_mission): JsonResponse
    {
        try {
            $proofs = Upload::where('id_user', $id_user)
                ->where('id_mission', $id_mission)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Riwayat bukti misi berhasil diambil.',
                'data' => $proofs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil riwayat bukti.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan daftar pengguna dengan total poin tertinggi.
     * @return JsonResponse
     */
    public function getTopUsers(): JsonResponse
    {
        try {
            $topUsers = User::where('role', 'karyawan')
                ->orderBy('total_point', 'desc')
                ->select('id_user', 'name', 'total_point')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Daftar pengguna teratas berhasil diambil.',
                'data' => $topUsers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil daftar pengguna teratas.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mencetak sertifikat untuk pengguna tertentu.
     * @param Request $request
     * @param int $id_user
     * @return JsonResponse
     */
    public function awardCertificate(Request $request, int $id_user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'certificate_name' => 'required|string|max:255',
            'file_path' => 'required|string', // Asumsi file sertifikat sudah diunggah
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::find($id_user);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Pengguna tidak ditemukan.'], 404);
            }

            // Buat entri baru di tabel certificates
            $certificate = Certificate::create([
                'id_user' => $id_user,
                'certificate_name' => $request->certificate_name,
                'file_path' => $request->file_path,
                'issued_date' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sertifikat berhasil diterbitkan.',
                'data' => $certificate
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menerbitkan sertifikat.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan semua sertifikat untuk pengguna tertentu.
     * @param int $id_user
     * @return JsonResponse
     */
    public function showUserCertificates(int $id_user): JsonResponse
    {
        try {
            $user = User::with('certificates')->find($id_user);
            
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Pengguna tidak ditemukan.'], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Sertifikat berhasil diambil.',
                'data' => $user->certificates
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil sertifikat.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
