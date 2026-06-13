<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SoapAuditService; 
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    protected $soapService;

    // Constructor Injection: Pastikan SoapAuditService sudah di-import
    public function __construct(SoapAuditService $soapService)
    {
        $this->soapService = $soapService;
    }

    // Nama metode diubah menjadi 'sendAudit' agar cocok dengan Route di api.php
    public function sendAudit(Request $request)
    {
        // 1. Logika Bisnis (Ambil data dari input Postman)
        $data = $request->all();

        // 2. Integrasi Audit SOAP
        try {
            // Mengirim data ke SOAP Service
            // Pastikan parameter di sini sesuai dengan kebutuhan SOAP Anda
            $receiptNumber = $this->soapService->sendAudit(
                'TEAM-25', 
                'UpdateInventory', 
                json_encode($data) // Mengirim data dari Postman ke audit
            );

            // 3. Respon sukses ke Postman
            return response()->json([
                'status' => 'success',
                'message' => 'Inventory berhasil diupdate dan sudah diaudit.',
                'receipt_number' => $receiptNumber
            ], 200);

        } catch (\Exception $e) {
            // Jika terjadi error pada SOAP, tangkap di sini
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal terhubung ke server audit: ' . $e->getMessage()
            ], 500);
        }
    }
}