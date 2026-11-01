# Panduan Kontribusi Tim Pengembang EcoFab ERP

Dokumen ini adalah acuan standar kerja bersama untuk 7 anggota tim pengembang proyek EcoFab ERP agar kolaborasi kode berjalan rapi, konsisten, dan bebas konflik.

---

## 🌿 1. Alur Percabangan Git (Git Workflow)

1. **Branch Utama:**
   - `main`: Menyimpan kode stabil siap rilis. **DILARANG KERAS** melakukan `git push` langsung ke `main`.
   - `develop`: Pusat integrasi seluruh fitur yang lolos review sprint. **DILARANG KERAS** melakukan `git push` langsung ke `develop`.
2. **Branch Fitur (Feature Branch):**
   - Setiap tugas / PBI fungsional dikerjakan pada branch tersendiri dari branch `develop`:
     ```bash
     git checkout develop
     git pull origin develop
     git checkout -b feature/FR-xx-nama-fitur
     ```
   - Contoh penamaan branch:
     - `feature/FR-03-kelola-departemen`
     - `feature/FR-07-purchase-order`
     - `feature/FR-10-material-sirkular`
     - `feature/FR-15-green-bom`
3. **Penggabungan Melalui Pull Request (PR):**
   - Buat PR dari `feature/FR-xx-...` menuju branch `develop`.
   - Wajib ditinjau dan disetujui (*Approved*) oleh **minimal 1 anggota tim lain (reviewer)** sebelum di-merge.
   - Semua automated test & linting di GitHub Actions harus berstatus hijau (*Passed*).

---

## 📝 2. Konvensi Pesan Commit (Conventional Commits)

Format pesan commit mengikuti format terstandar:
```
<tipe>(<lingkup/FR>): <deskripsi singkat dalam bahasa indonesia>
```

### Tipe Commit:
- `feat`: Penambahan fitur baru (misal: `feat(FR-10): implementasi form tambah material`)
- `fix`: Perbaikan bug atau galat (misal: `fix(auth): tangani redirect saat sesi kedaluwarsa`)
- `refactor`: Perubahan struktur kode tanpa mengubah fungsionalitas (misal: `refactor(services): optimasi query kalkulasi emisi`)
- `test`: Penambahan atau penyesuaian unit/feature test (misal: `test(FR-10): tambahkan feature test validasi stok minimum`)
- `docs`: Perubahan atau penambahan dokumentasi (misal: `docs: perbarui panduan setup di README`)
- `chore`: Tugas pemeliharaan, dependensi, atau konfigurasi (misal: `chore: update dependencies composer`)

---

## 🗄️ 3. Aturan Nama Migrasi & Model Basis Data

1. **Aturan Penamaan Migrasi:**
   - Gunakan format standar: `create_{nama_tabel}_table` dalam bahasa Inggris jamak (*plural snake_case*):
     - `create_departments_table`
     - `create_materials_table`
     - `create_work_orders_table`
     - `create_waste_records_table`
2. **Tipe Data & Standar Kolom:**
   - Gunakan `decimal(12, 2)` atau `decimal(10, 2)` untuk kuantitas dan emisi, jangan gunakan `float`.
   - Gunakan `decimal(14, 2)` untuk nominal uang Rupiah.
   - Kolom yang sering dijadikan filter atau pencarian (misal: `code`, `status`, `sku`) **wajib diberi indeks** (`$table->index(...)`).
   - Gunakan foreign key yang ketat dengan penanganan referensial yang jelas (`constrained()->cascadeOnDelete()` atau `nullOnDelete()`).

---

## 📐 4. Standar Kode & PSR-12

Proyek ini mewajibkan kepatuhan terhadap standar **PSR-12**.
Sebelum membuat commit atau membuka Pull Request, **wajib** jalankan linter lokal:

```bash
# Periksa kepatuhan PSR-12 (Mode Test)
composer lint

# Perbaiki style otomatis menggunakan Laravel Pint
composer run lint:fix
```

---

## 🧪 5. Menjalankan Pengujian Otomatis

Setiap penambahan fitur baru harus disertai pengujian (Feature Test dan/atau Unit Test):

```bash
# Jalankan seluruh suite test
php artisan test

# Jalankan test khusus untuk modul Anda
php artisan test --filter=MaterialTest
```
Pastikan **seluruh pengujian lulus 100%** sebelum mengajukan Pull Request.
