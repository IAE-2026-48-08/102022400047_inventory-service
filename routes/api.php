<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\InventoryController;

/*
|--------------------------------------------------------------------------
| API Routes - Inventory Service (Dhika)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    
    // Endpoint 1: Melihat daftar barang yang tersedia di gudang
    Route::get('/inventories', [InventoryController::class, 'index']);
    
    // Endpoint 2: Mengambil detail barang dan mengecek ketersediaan stok
    Route::get('/inventories/{id}', [InventoryController::class, 'show']);
    
    // Endpoint 3: Memproses barang order sekaligus mencatat hasil QC
    Route::post('/inventories/qc', [InventoryController::class, 'storeQC']);
    
});