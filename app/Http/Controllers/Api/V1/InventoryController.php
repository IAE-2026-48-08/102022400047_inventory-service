<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    // Respons Data untuk Endpoint 1 (GET List)
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mengambil daftar barang di gudang',
            'data' => [
                ['id' => 1, 'item_name' => 'Kardus Box Packing', 'stock' => 150],
                ['id' => 2, 'item_name' => 'Bubble Wrap 50m', 'stock' => 45]
            ]
        ], 200);
    }

    // Respons Data untuk Endpoint 2 (GET Detail per ID)
    public function show($id)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Detail ketersediaan stok barang ditemukan',
            'data' => [
                'id' => (int)$id,
                'item_name' => 'Kardus Box Packing',
                'stock' => 150,
                'status_ketersediaan' => 'READY_STOCK'
            ]
        ], 200);
    }

    // Respons Data untuk Endpoint 3 (POST QC)
    public function storeQC(Request $request)
    {
        $orderId = $request->input('order_id', 101);
        $qcStatus = $request->input('qc_status', 'PASSED');
        $notes = $request->input('notes', 'Barang mulus lolos inspeksi');

        return response()->json([
            'status' => 'success',
            'message' => 'Memproses barang order sekaligus mencatat hasil QC',
            'receipt' => [
                'order_id' => $orderId,
                'qc_status' => $qcStatus,
                'notes' => $notes,
                'processed_at' => now()->toDateTimeString()
            ]
        ], 201);
    }
}