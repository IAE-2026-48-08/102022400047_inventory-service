<?php

namespace App\Jobs;

use App\Models\AuditLog; // Import model yang baru dibuat
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessInventoryMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Variabel publik untuk menampung data
    public $data;

    /**
     * Create a new job instance.
     *
     * @param mixed $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Log::info('--- Job ProcessInventoryMessage Dimulai ---');

            // 1. Simpan ke Database
            // Pastikan $this->data memiliki key 'inventory_id' dan 'quantity'
            AuditLog::create([
                'inventory_id' => $this->data['inventory_id'],
                'quantity'     => $this->data['quantity'],
                'status'       => 'processed' // Menambahkan status default
            ]);

            Log::info("Data berhasil disimpan ke database.");

            // Cek apakah data berupa array atau string untuk menghindari error
            $logData = is_array($this->data) ? json_encode($this->data) : $this->data;
            
            Log::info("Data yang diterima: " . $logData);
            
            Log::info('--- Job Berhasil Diproses ---');

        } catch (\Exception $e) {
            // Jika terjadi error, log pesan errornya
            Log::error("Job Gagal: " . $e->getMessage());
            
            // Melempar kembali error agar status job di queue menjadi FAILED
            throw $e;
        }
    }
}