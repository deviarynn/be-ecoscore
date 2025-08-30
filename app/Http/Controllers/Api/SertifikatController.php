<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class SertifikatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function showByIdUser(string $id)
    {
        // Cari user berdasarkan ID dan muat relasi 'certificates'
        $user = User::with('certificates')->find($id);
    
        // Cek jika user tidak ditemukan
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }
    
        // Kembalikan semua sertifikat milik user
        return response()->json([
            'success' => true,
            'message' => 'List of certificates for the user.',
            'data' => [
                'user_id' => $user->id_user,
                'user_name' => $user->name,
                'certificates' => $user->certificates
            ]
        ]);
    }
}
