<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SoapAuditService
{
    /**
     * Mengirim log audit ke server SOAP dosen.
     * 
     * @param string $teamId
     * @param string $actionName
     * @param mixed $data
     * @return string|null
     */
    public function sendAudit($teamId, $actionName, $data)
    {
        // 1. Persiapkan data log
        $logContent = is_array($data) ? json_encode($data) : $data;

        // 2. Susun XML Envelope (Wajib menggunakan CDATA untuk data JSON)
        // Menghilangkan spasi setelah titik dua (:) agar tag XML valid dan bisa diparsing server
        $xmlBody = '<?xml version="1.0" encoding="UTF-8"?>
        <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:iae="http://iae.central/audit">
            <soap:Body>
                <iae:AuditRequest>
                    <iae:TeamID>' . $teamId . '</iae:TeamID>
                    <iae:ActivityName>' . $actionName . '</iae:ActivityName>
                    <iae:LogContent><![CDATA[' . $logContent . ']]></iae:LogContent>
                </iae:AuditRequest>
            </soap:Body>
        </soap:Envelope>';

        // 3. Ambil SSO Token secara dinamis jika tidak ada di session (untuk stateless worker/grader)
        $token = session('api_token') ?? env('SSO_ACCESS_TOKEN');

        $email = env('SSO_USER_EMAIL');
        $apiKey = env('SSO_API_KEY');
        if (!$token && $email && $apiKey) {
            try {
                $tokenResponse = Http::timeout(5)
                    ->withoutVerifying()
                    ->post('https://iae-sso.virtualfri.id/api/v1/auth/token', [
                        'email'    => $email,
                        'password' => $apiKey
                    ]);
                if ($tokenResponse->successful()) {
                    $resData = $tokenResponse->json();
                    $token = $resData['access_token'] ?? ($resData['token'] ?? null);
                }
            } catch (\Exception $e) {
                Log::warning("Gagal mengambil dynamic M2M token: " . $e->getMessage());
            }
        }

        // 4. Kirim ke Endpoint Dosen
        $response = Http::withToken($token)
            ->withoutVerifying()
            ->withHeaders(['Content-Type' => 'text/xml'])
            ->post('https://iae-sso.virtualfri.id/soap/v1/audit', $xmlBody);

        // 5. Proses Respons & Ambil Receipt Number
        if ($response->successful()) {
            $xmlResponse = $response->body();
            
            // Mengambil isi dari tag ReceiptNumber menggunakan regex yang toleran terhadap namespace/prefix
            preg_match('/<(?:[a-zA-Z0-9\-]+:)?ReceiptNumber>(.*?)<\/(?:[a-zA-Z0-9\-]+:)?ReceiptNumber>/', $xmlResponse, $matches);
            
            return $matches[1] ?? 'FAILED-TO-EXTRACT';
        }

        // Jika gagal, catat di log agar mudah di-debug
        Log::error("SOAP Error (" . $teamId . "): " . $response->body());
        return null;
    }
}