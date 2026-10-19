<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Pengguna;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Cari User berdasarkan Email
        $user = Pengguna::where('email', $request->email)->first();

        // 3. Cek Password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau Password salah!'
            ], 401);
        }

        // 4. Kirim Respon Sukses
        return response()->json([
            'message' => 'Login Berhasil',
            'user' => [
                'id' => $user->id,
                'name' => $user->nama,
                'email' => $user->email,
            ]
        ], 200);
    }
}
