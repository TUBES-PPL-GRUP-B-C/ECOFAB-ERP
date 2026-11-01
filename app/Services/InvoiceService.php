<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

/**
 * Service untuk pembuatan dan pencetakan faktur/invoice penjualan
 * dan surat jalan dalam format PDF (FR-21 / TR-07).
 */
class InvoiceService
{
    /**
     * Hasilkan PDF Faktur Penjualan berdasarkan Sales Order atau Surat Jalan.
     *
     * @param int $deliveryId
     * @return Response
     */
    public function generateInvoicePdf(int $deliveryId): Response
    {
        $data = [
            'deliveryId' => $deliveryId,
            'title' => 'Faktur Penjualan EcoFab ERP',
        ];

        $pdf = Pdf::loadView('penjualan.invoice_pdf', $data);

        return $pdf->download("invoice-{$deliveryId}.pdf");
    }

    /**
     * Hasilkan PDF Surat Jalan Pengiriman.
     */
    public function generateDeliveryNotePdf(int $deliveryId): Response
    {
        $data = [
            'deliveryId' => $deliveryId,
            'title' => 'Surat Jalan Pengiriman',
        ];

        $pdf = Pdf::loadView('penjualan.surat_jalan_pdf', $data);

        return $pdf->download("surat-jalan-{$deliveryId}.pdf");
    }
}
