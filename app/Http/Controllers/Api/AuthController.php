<?php
// File: app/Http/Controllers/Api/AuthController.php
// Deskripsi: Controller untuk mengelola otentikasi (login/logout).

namespace App\Http\Controllers\Api\Karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Cari pengguna berdasarkan username
        $user = User::where('username', $request->username)->first();

        // Cek apakah user ada dan password cocok, serta role-nya adalah 'karyawan'
        if (!$user || !Hash::check($request->password, $user->password) || $user->role !== 'karyawan') {
            return response()->json([
                'message' => 'Username atau password salah.'
            ], 401);
        }

        // Buat token autentikasi
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil!',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        // Hapus token yang sedang digunakan
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil!'
        ]);
    }
}