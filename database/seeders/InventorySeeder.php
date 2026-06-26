<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventorySeeder extends Seeder
{
    public function run()
    {
        DB::table('inventories')->insert([
            [
                'nama_barang' => 'Laptop ASUS',
                'stok' => 10,
                'status_qc' => 'PASSED',
                'kondisi_barang' => 'Baru',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_barang' => 'Mouse Wireless',
                'stok' => 50,
                'status_qc' => 'PENDING',
                'kondisi_barang' => 'Baru',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}