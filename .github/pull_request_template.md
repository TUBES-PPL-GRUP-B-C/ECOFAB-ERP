## 📌 Identitas Fitur / PBI
- **ID Kebutuhan Fungsional (FR):** `[misal: FR-10]`
- **Nama Modul:** `[misal: Gudang & Material Sirkular]`
- **Penanggung Jawab:** @username

---

## 📝 Ringkasan Perubahan
Jelaskan secara ringkas perubahan yang dilakukan dan tujuan bisnisnya:
- 

---

## 🧪 Panduan Cara Menguji Perubahan
Langkah-langkah bagi reviewer untuk memverifikasi fitur secara manual di lokal:
1. Jalankan migrasi: `php artisan migrate` (jika ada migrasi baru)
2. Masuk sebagai: `[misal: gudang@ecofab.local / password123]`
3. Buka halaman: `[misal: /gudang/material/create]`
4. Masukkan data uji dan verifikasi hasilnya:

---

## 📷 Tangkapan Layar (Screenshot / UI Proof)
*Lampirkan tangkapan layar tampilan antarmuka sebelum dan sesudah perubahan jika ada perubahan UI.*

---

## ✅ Checklist Pengembang
- [ ] Kode telah mengikuti standar **PSR-12** (`composer lint` lulus tanpa galat).
- [ ] Telah membuat dan menjalankan pengujian otomatis (`php artisan test` lulus 100%).
- [ ] Seluruh teks antarmuka dan pesan validasi berbahasa Indonesia yang konsisten.
- [ ] Format angka, nominal Rupiah, dan tanggal menggunakan helper `format_angka()`, `format_rupiah()`, `format_tanggal()`.
- [ ] Tidak ada konflik penggabungan (*merge conflicts*) dengan branch target (`develop`).
- [ ] Tidak menyertakan kredensial nyata atau file `.env` ke dalam commit.
