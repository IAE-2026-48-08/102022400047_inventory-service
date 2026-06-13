<?php

namespace App\Services;

class SoapAuditService
{
    public function sendAudit($team, $action, $data)
    {
        // Logika koneksi SOAP Anda di sini
        // Untuk testing, kita kembalikan string dummy
        return "RECEIPT-" . uniqid();
    }
}