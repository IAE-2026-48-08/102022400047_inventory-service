<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    // Tambahkan baris ini
    protected $fillable = ['nama_barang', 'stok', 'status_qc', 'kondisi_barang'];
}