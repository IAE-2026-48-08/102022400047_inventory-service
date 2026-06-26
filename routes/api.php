<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\InventoryController;
use App\Http\Controllers\SsoController;
use App\Http\Middleware\CheckXIntegrationKey;

// Public Route
Route::post('/v1/sso/callback', [SsoController::class, 'handleCallback']);

// Protected Routes - wajib kirim header X-IAE-KEY: 102022400047
Route::prefix('v1')->middleware([CheckXIntegrationKey::class])->group(function () {
    Route::get('/inventories', [InventoryController::class, 'index']);
    Route::get('/inventories/{id}', [InventoryController::class, 'show']);
    Route::post('/inventories', [InventoryController::class, 'store']);
    Route::post('/inventories/qc', [InventoryController::class, 'storeQC']);
    Route::post('/inventory/audit', [InventoryController::class, 'sendAudit']);
});