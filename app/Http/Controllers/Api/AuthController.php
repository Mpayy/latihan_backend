<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Langkah 1: Siapkan "Pintu Tujuannya"
    public function register(Request $request){
        // Langkah 2: Siapkan Jaring Pengaman (Try-Catch)
        try {
            // Langkah 3: Siapkan "Satpam Pintu Depan" (Validator) di dalam try
            $validator = Validator::make($request->only('name', 'username', 'email', 'password', 'bio', 'profile_photo'), [
                'name' => 'required|string',
                'username' => 'required|string|unique:users,username',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8|confirmed',
                'bio' => 'nullable|string|max:1000',
                'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            // Langkah 4: Skenario Ditolak Satpam (Validasi Gagal)
            if ($validator->fails()) {
                return ResponseHelper::error('Validation Error', $validator->errors(), 422);
            }

            // Langkah 5: Skenario Diterima (Masukkan Data ke Gudang Database)
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => $request->password,
                'bio' => $request->bio,
                'profile_photo' => $request->profile_photo
            ]);

            return ResponseHelper::success($user, 'Register Berhasil', 201);

        } catch (\Throwable $th) {
            // Langkah 7: Memberi Pesan di Jaring Pengaman (Update si Catch)
            return ResponseHelper::error('Internal Server Error!!', $th->getMessage(), 500);
        }
    }

    // Membuat Fitur Login (Mendapatkan Tiket Masuk)
    public function login(Request $request){
        // Langkah 1: Siapkan Fungsinya & Jaring Pengaman
        try {
            // Langkah 2: Satpam Penjaga Pintu Masuk (Validator)
            $validator = Validator::make($request->only('email', 'password'), [
                'email' => 'required|email',
                'password' => 'required|min:8'
            ]);

            // Langkah 2: Satpam Penjaga Pintu Masuk (Validator)
            if ($validator->fails()) {
                return ResponseHelper::error('Validation Error', $validator->errors(), 422);
            }

            // Langkah 3: Cek KTP (Cari User di Database)
            $user = User::where('email', $request->email)->first();
            if (!$user || !Hash::check($request->password, $user->password)) {
                return ResponseHelper::error('Unauthorized', 'Email atau Password salah', 401);
            }

            // Langkah 5: Cetak Tiket Masuk (Sanctum Token) 
            // NOTE: tambahkan HasApiTokens pada User.php agar createToken berfungsi
            $token = $user->createToken('auth_token')->plainTextToken;

            // Langkah 6: Kembalikan Respons Sukses Bersama Token
            return response()->json([
                'status' => true,
                'message' => 'Login Berhasil',
                'data' => $user,
                'token' => $token
            ]);

        } catch (\Throwable $th) {
            return ResponseHelper::error('Internal Server Error!!', $th->getMessage(), 500);
        }
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();
        $token->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }
}
