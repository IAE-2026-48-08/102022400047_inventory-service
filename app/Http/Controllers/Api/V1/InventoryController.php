<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\ProcessInventoryMessage;
use App\Models\Inventory;
use App\Services\SoapAuditService;
use App\Http\Resources\InventoryResource; // Pastikan import ini ada

class InventoryController extends Controller
{
    protected $soapService;

    public function __construct(SoapAuditService $soapService)
    {
        $this->soapService = $soapService;
    }

    /**
     * @OA\Get(
     * path="/api/v1/inventories",
     * summary="Lihat daftar barang",
     * security={{"bearerAuth":{}}},
     * @OA\Response(response=200, description="Berhasil mengambil data"),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index()
    {
        $items = Inventory::all();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mengambil daftar barang di gudang',
            'data' => InventoryResource::collection($items)
        ], 200);
    }

    /**
     * @OA\Get(
     * path="/api/v1/inventories/{id}",
     * summary="Lihat detail barang",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Data ditemukan"),
     * @OA\Response(response=404, description="Barang tidak ditemukan")
     * )
     */
    public function show($id)
    {
        try {
            // Menggunakan findOrFail untuk otomatis melempar Exception jika data tidak ada
            $item = Inventory::findOrFail($id);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Detail ketersediaan stok barang ditemukan',
                'data' => new InventoryResource($item) 
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Barang tidak ditemukan'
            ], 404);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/v1/inventories/qc",
     * summary="Proses QC Barang",
     * security={{"bearerAuth":{}}},
     * @OA\Response(response=201, description="QC Berhasil diproses")
     * )
     */
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

    /**
     * @OA\Post(
     * path="/api/v1/inventories",
     * summary="Tambah ke antrean (Queue)",
     * security={{"bearerAuth":{}}},
     * @OA\Response(response=202, description="Data diproses di antrean")
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'inventory_id' => 'required|integer',
            'quantity' => 'required|integer',
        ]);

        ProcessInventoryMessage::dispatch($data);

        return response()->json([
            'message' => 'Data sedang diproses di antrean!',
        ], 202);
    }

    /**
     * @OA\Post(
     * path="/api/v1/inventories/audit",
     * summary="Audit inventori (SOAP)",
     * security={{"bearerAuth":{}}},
     * @OA\Response(response=200, description="Berhasil diaudit"),
     * @OA\Response(response=500, description="Gagal audit")
     * )
     */
    public function sendAudit(Request $request)
    {
        $data = $request->all();

        try {
            $receiptNumber = $this->soapService->sendAudit(
                'TEAM-03', 
                'UpdateInventory', 
                json_encode($data)
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Inventory berhasil diupdate dan sudah diaudit.',
                'receipt_number' => $receiptNumber
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal terhubung ke server audit: ' . $e->getMessage()
            ], 500);
        }
    }
}