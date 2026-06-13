<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SsoController;
use App\Http\Controllers\Api\V1\MessageController;
// Menggunakan alias untuk membedakan InventoryController
use App\Http\Controllers\InventoryController as SimpleInventoryController;
use App\Http\Controllers\Api\V1\InventoryController as ApiInventoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// 1. Public Routes
Route::post('/v1/sso/callback', [SsoController::class, 'handleCallback']);

// 2. Route untuk akses langsung (Public)
Route::post('/inventory', [SimpleInventoryController::class, 'sendAudit'])->name('inventory.audit.simple');

// 3. RUTE RABBITMQ (Public untuk keperluan testing)
Route::post('/v1/messages/publish', [MessageController::class, 'publish']);

// 4. Protected Routes (v1) - Memerlukan Token Auth
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    
    // Rute Inventaris
    Route::get('/inventories', [ApiInventoryController::class, 'index'])->name('inventories.index');
    Route::get('/inventories/{id}', [ApiInventoryController::class, 'show'])->name('inventories.show');
    Route::post('/inventories/qc', [ApiInventoryController::class, 'storeQC'])->name('inventories.storeQC');
    
    // Rute untuk Antrean (Queueing) - SEKARANG TERLINDUNGI
    Route::post('/inventory/store', [ApiInventoryController::class, 'store'])->name('inventories.store');
    
    // Rute Audit SOAP
    Route::post('/inventory/audit', [ApiInventoryController::class, 'sendAudit'])->name('inventory.audit');
    
});