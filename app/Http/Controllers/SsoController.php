<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SsoController extends Controller
{
    public function handleCallback(Request $request)
    {
        // 1. Ambil data dari input (email & password warga)
        $email = $request->input('email');
        $password = $request->input('password');

        if (!$email || !$password) {
            return response()->json(['message' => 'Email dan Password diperlukan'], 400);
        }

        try {
            // 2. Request ke IAE SSO Server
            $response = Http::timeout(10)->post('https://iae-sso.virtualfri.id/api/v1/auth/token', [
                'email' => $email,
                'password' => $password
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Asumsi respon dari IAE mengandung user data atau token
                // Anda mungkin perlu memproses $data['access_token'] jika ada
                
                return response()->json([
                    'message' => 'Login SSO IAE Berhasil',
                    'token'   => $data['access_token'] ?? 'Token diterima'
                ]);
            }

            return response()->json([
                'message' => 'Gagal login ke IAE', 
                'error' => $response->json()
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('SSO IAE Connection Failed: ' . $e->getMessage());
            return response()->json(['message' => 'Server IAE tidak merespon', 'error' => $e->getMessage()], 503);
        }
    }
}