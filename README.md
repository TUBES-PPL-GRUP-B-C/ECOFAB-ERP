# EcoFab ERP - Platform Manufaktur Sirkular Berkelanjutan (SDG 12)

EcoFab adalah sistem Enterprise Resource Planning (ERP) pabrik berbasis web terintegrasi yang mengelola alur bisnis konvensional (SDM, pengadaan, gudang, produksi, dan penjualan), sekaligus menghadirkan tiga inovasi inti:
1. **Green BoM**: Kalkulasi jejak karbon (*embodied carbon* dalam kg CO₂e) dan biaya produksi (Rp) per unit secara transparan.
2. **Bursa Limbah Sirkular**: Katalog bursa limbah terbuka agar sisa potongan kayu FSC, rotan, dan serbuk daur ulang dapat dimanfaatkan pengrajin lokal tanpa dibuang ke TPA.
3. **Eco-Passport**: Paspor digital produk dengan verifikasi QR Code pada kemasan untuk transparansi asal bahan baku dan kepatuhan standar lingkungan tanpa mengekspos rahasia biaya internal.

Proyek ini mendukung pencapaian **SDG 12: Konsumsi dan Produksi yang Bertanggung Jawab** (Target 12.2, 12.5, 12.6, dan 12.8).

---

## 🛠️ Stack Teknologi

- **Backend**: PHP 8.2+, Laravel 12.x (MVC Monolit Modular + Service Layer, PSR-12)
- **Basis Data**: MySQL 8.x / MariaDB 10.x
- **Frontend**: Blade Template, Tailwind CSS v4, Chart.js, Vite
- **Paket Khusus**:
  - `spatie/laravel-permission`: Manajemen Peran & Hak Akses (RBAC)
  - `simplesoftwareio/simple-qrcode`: Pembuatan QR Code Paspor Digital
  - `barryvdh/laravel-dompdf`: Ekspor Faktur Penjualan & Surat Jalan PDF
- **Pengujian & Kualitas Kode**: PHPUnit, Laravel Pint (PSR-12 Preset)

---

## 🚀 Panduan Setup Proyek (Dari Nol)

### 1. Prasyarat Sistem
- PHP >= 8.2 (ekstensi: `pdo_mysql`, `openssl`, `mbstring`, `gd`, `bcmath`)
- Composer >= 2.x
- Node.js >= 18.x & npm
- Layanan MySQL 8 berjalan di `localhost:3306`

### 2. Kloning & Pemasangan Dependensi
```bash
# Salin konfigurasi environment
cp .env.example .env

# Pasang dependensi PHP dan Node
composer install
npm install
```

### 3. Konfigurasi Basis Data & Kunci Aplikasi
Pastikan basis data `ecofab` telah dibuat di MySQL:
```sql
CREATE DATABASE IF NOT EXISTS ecofab CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Jalankan perintah generate key dan migrasi + seeding awal:
```bash
php artisan key:generate
php artisan migrate:fresh --seed
```

### 4. Menjalankan Server Pengembangan
Jalankan server aplikasi dan Vite (bisa di dua terminal terpisah):
```bash
# Terminal 1: Backend
php artisan serve

# Terminal 2: Vite compiler
npm run dev
```
Akses sistem di peramban: `http://localhost:8000`

---

## 🔑 Akun Uji Coba Pengguna (Local/Demo)

Semua akun dummy awal menggunakan kata sandi: **`password123`**

| Peran (Role) | Kelompok Peran | Email | Hak Akses Utama |
|---|---|---|---|
| **Admin Sistem** | Admin Pabrik | `admin@ecofab.local` | Akses penuh seluruh modul, kelola user & role, setujui PO & order limbah |
| **Staf HR** | Operasional | `hr@ecofab.local` | CRUD Departemen, Karyawan, dan Absensi |
| **Staf Pembelian** | Operasional | `pembelian@ecofab.local` | CRUD Supplier, ajukan Purchase Order (PO) |
| **Staf Gudang** | Operasional | `gudang@ecofab.local` | CRUD Gudang, Material Sirkular, Mutasi Stok, GRN Penerimaan |
| **Staf Produksi** | Operasional | `produksi@ecofab.local` | Mesin, Produk, Green BoM, Work Order, Limbah Produksi, Eco-Passport |
| **Staf Penjualan** | Komersial | `penjualan@ecofab.local` | Customer, Sales Order (SO), Pengiriman, Listing Bursa Limbah |

> **Catatan Pengujian:** Di environment `local`, tersedia kontrol **"SIMULASI PERAN"** di header halaman `/dashboard` untuk berpindah peran secara cepat tanpa perlu logout.

---

## 📂 Struktur Modular Aplikasi

Agar 7 anggota tim dapat bekerja paralel tanpa bentrok (*merge conflicts*):

```
app/
├── Helpers/helpers.php           # Helper format angka Indonesia (12.480, 4,2, Rupiah, CO2e)
├── Http/Controllers/
│   ├── Admin/                    # Manajemen Pengguna & Peran (UserController)
│   ├── Auth/                     # Login & Logout (AuthenticatedSessionController)
│   ├── Hr/                       # Controller modul HR (Departemen, Karyawan, Absensi)
│   ├── Pembelian/                # Controller modul Pengadaan (Supplier, PO)
│   ├── Gudang/                   # Controller modul Gudang (Gudang, Material, Mutasi, GRN)
│   ├── Produksi/                 # Controller modul Manufaktur (Mesin, Produk, BoM, WO, Limbah)
│   ├── Penjualan/                # Controller modul Penjualan (Customer, SO, Surat Jalan)
│   ├── Green/                    # Controller Faktor Emisi (FaktorEmisiController)
│   ├── Bursa/                    # Controller Listing & Order Bursa Limbah
│   ├── Passport/                 # Controller Eco-Passport internal
│   └── Public/                   # Controller Katalog Publik & QR Scan publik
├── Models/
│   └── User.php                  # Model Pengguna dengan trait Spatie HasRoles & helper peran
├── Services/                     # Service Layer bersama
│   ├── CarbonCalculatorService.php # Satu-satunya rumus kalkulasi emisi & biaya
│   ├── StockService.php          # Logika pergerakan stok gudang & anti stok minus
│   ├── WasteMarketService.php    # Siklus limbah produksi & pesanan bursa
│   ├── EcoPassportService.php    # Pembuatan kode unik, QR Code, dan data rantai pasok
│   └── InvoiceService.php        # Cetak invoice PDF & surat jalan
└── Traits/
    ├── Searchable.php            # Trait pencarian & filter multi-kolom Eloquent (FR-28)
    └── Auditable.php             # Trait pencatat created_by & updated_by otomatis (NFR-12)

config/
└── menu.php                      # Single source of truth navigasi sidebar per modul

routes/
├── web.php                       # Router utama (memuat otomatis routes/modules/*.php)
├── public.php                    # Rute publik bursa katalog & scan passport tanpa auth
└── modules/                      # File rute modular per tanggung jawab anggota tim:
    ├── hr.php (Prefix /hr)
    ├── pembelian.php (Prefix /pembelian)
    ├── gudang.php (Prefix /gudang)
    ├── produksi.php (Prefix /produksi)
    ├── penjualan.php (Prefix /penjualan)
    ├── green.php (Prefix /green)
    ├── bursa.php (Prefix /bursa)
    └── passport.php (Prefix /passport)

resources/views/
├── components/                   # Komponen Blade bersama (<x-card>, <x-stat-card>, <x-badge>, dll)
├── layouts/                      # Layout utama (app.blade.php dan public.blade.php)
├── auth/                         # Halaman login
├── admin/                        # Tampilan manajemen user & role
└── {modul}/                      # View khusus per modul
```

---

## 💡 Panduan Menambah Fitur Baru bagi Anggota Tim

1. **Buat Controller:** Buat controller di folder modul Anda, misalnya `app/Http/Controllers/Gudang/MaterialController.php`.
2. **Definisikan Rute:** Tambahkan rute di file modul Anda pada `routes/modules/{modul}.php` (misal `routes/modules/gudang.php`). Anda **tidak perlu** mengedit `routes/web.php`.
3. **Pencarian & Filter (FR-28):** Gunakan trait `Searchable` pada Model Anda:
   ```php
   use App\Traits\Searchable;
   class Material extends Model {
       use Searchable;
   }
   
   // Di Controller:
   $materials = Material::search($request->cari, ['code', 'name', 'category'])
       ->filter($request->only(['category']))
       ->latest()
       ->paginate(15)
       ->withQueryString();
   ```
4. **Helper Angka Indonesia:** Gunakan helper global di view atau controller:
   - `format_angka(12480)` &rarr; `12.480`
   - `format_angka(4.2, 1)` &rarr; `4,2`
   - `format_rupiah(4250000)` &rarr; `Rp 4.250.000`
   - `format_emisi(12480)` &rarr; `12.480,0 kg CO₂e`
   - `format_tanggal($record->created_at)` &rarr; `01 Nov 2026`
5. **Navigasi Sidebar:** Tambahkan entri baru di `config/menu.php` dengan menentukan rute dan `permission` yang sesuai.

---

## 🧪 Pengujian & Standar Kode

```bash
# Menjalankan seluruh pengujian (Feature & Unit)
php artisan test

# Memeriksa kepatuhan standar kode PSR-12
composer lint

# Memperbaiki formatting kode otomatis via Pint
composer run lint:fix

# Membangun bundle aset produksi
npm run build
```
