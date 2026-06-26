<?php

namespace App\GraphQL\Mutations; // PERHATIKAN: A, G, L, M harus HURUF BESAR

final class StoreQC // PERHATIKAN: S, Q, C harus HURUF BESAR sesuai nama file
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        return [
            'status' => 'success',
            'message' => 'Memproses barang order sekaligus mencatat hasil QC',
            'order_id' => $args['order_id'],
            'qc_status' => $args['qc_status'],
            'notes' => $args['notes'] ?? null,
        ];
    }
}