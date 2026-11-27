<?php

namespace App\Services;

use App\Models\EcoPassport;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EcoPassportService
{
    /** Buat draft untuk batch WO selesai; data produk dan emisi tetap dibaca dari WO. */
    public function createDraft(WorkOrder $workOrder): EcoPassport
    {
        return DB::transaction(function () use ($workOrder) {
            $locked = WorkOrder::query()->lockForUpdate()->findOrFail($workOrder->id);
            abort_unless($locked->isCompleted(), 409, 'Work Order harus selesai sebelum dibuatkan Eco-Passport.');

            $passport = new EcoPassport();
            $passport->work_order_id = $locked->id;
            $passport->unique_code = 'ECO-PASS-' . strtoupper(Str::uuid()->toString());
            $passport->save();

            return $passport;
        }, 3);
    }

    public function publish(EcoPassport $passport): void
    {
        $this->transition($passport, EcoPassport::STATUS_DRAFT, EcoPassport::STATUS_PUBLISHED);
    }

    public function withdraw(EcoPassport $passport): void
    {
        $this->transition($passport, EcoPassport::STATUS_PUBLISHED, EcoPassport::STATUS_WITHDRAWN);
    }

    private function transition(EcoPassport $passport, string $from, string $to): void
    {
        $updated = EcoPassport::query()
            ->whereKey($passport->getKey())
            ->where('publication_status', $from)
            ->update(['publication_status' => $to]);

        abort_unless($updated === 1, 409, 'Status Eco-Passport sudah berubah atau tidak sesuai dengan tindakan ini.');
    }

    /**
     * Hasilkan QR Code SVG/PNG untuk kode paspor.
     *
     * @param string $passportCode
     * @return string Konten QR code
     */
    public function generateQrCode(string $passportCode): string
    {
        $url = url('/passport/' . $passportCode);

        return QrCode::size(250)->generate($url);
    }

    /**
     * Ambil data publik untuk ditampilkan di halaman hasil pindai QR (tanpa harga/biaya internal).
     */
    public function getPublicPassportData(string $passportCode): ?array
    {
        return null;
    }
}
