<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Navigasi Sidebar EcoFab ERP
    |--------------------------------------------------------------------------
    | Menu dikelompokkan sesuai modul bisnis.
    | Menu akan otomatis tersembunyi jika pengguna tidak memiliki izin (permission).
    | Anggota tim cukup menambahkan entri baru di file ini untuk modul mereka.
    */

    'groups' => [
        [
            'title' => 'INTI SISTEM',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'route' => 'dashboard',
                    'active' => 'dashboard',
                    'icon' => 'dashboard',
                    'permission' => 'dashboard.view',
                ],
            ],
        ],

        [
            'title' => 'SDM & ORGANISASI',
            'items' => [
                [
                    'label' => 'Departemen',
                    'route' => 'hr.departemen.index',
                    'active' => 'hr/departemen*',
                    'icon' => 'building',
                    'permission' => 'hr.view',
                ],
                [
                    'label' => 'Karyawan',
                    'route' => 'hr.karyawan.index',
                    'active' => 'hr/karyawan*',
                    'icon' => 'users',
                    'permission' => 'hr.view',
                ],
                [
                    'label' => 'Absensi',
                    'route' => 'hr.absensi.index',
                    'active' => 'hr/absensi*',
                    'icon' => 'calendar',
                    'permission' => 'hr.view',
                ],
            ],
        ],

        [
            'title' => 'PENGADAAN',
            'items' => [
                [
                    'label' => 'Supplier',
                    'route' => 'pembelian.supplier.index',
                    'active' => 'pembelian/supplier*',
                    'icon' => 'truck',
                    'permission' => 'pembelian.view',
                ],
                [
                    'label' => 'Purchase Order (PO)',
                    'route' => 'pembelian.po.index',
                    'active' => 'pembelian/po*',
                    'icon' => 'file-text',
                    'permission' => 'pembelian.view',
                ],
                [
                    'label' => 'Penerimaan (GRN)',
                    'route' => 'gudang.grn.index',
                    'active' => 'gudang/grn*',
                    'icon' => 'clipboard-check',
                    'permission' => 'gudang.view',
                ],
            ],
        ],

        [
            'title' => 'INVENTARIS & GUDANG',
            'items' => [
                [
                    'label' => 'Gudang',
                    'route' => 'gudang.gudang.index',
                    'active' => 'gudang/gudang*',
                    'icon' => 'archive',
                    'permission' => 'gudang.view',
                ],
                [
                    'label' => 'Material Sirkular',
                    'route' => 'gudang.material.index',
                    'active' => 'gudang/material*',
                    'icon' => 'layers',
                    'permission' => 'gudang.view',
                ],
                [
                    'label' => 'Mutasi Stok',
                    'route' => 'gudang.mutasi.index',
                    'active' => 'gudang/mutasi*',
                    'icon' => 'repeat',
                    'permission' => 'gudang.view',
                ],
            ],
        ],

        [
            'title' => 'MANUFAKTUR SIRKULAR',
            'items' => [
                [
                    'label' => 'Faktor Emisi (CO₂e)',
                    'route' => 'green.faktor-emisi.index',
                    'active' => 'green/faktor-emisi*',
                    'icon' => 'cloud',
                    'permission' => 'emisi.manage',
                ],
                [
                    'label' => 'Mesin & Lini Produksi',
                    'route' => 'produksi.mesin.index',
                    'active' => 'produksi/mesin*',
                    'icon' => 'cpu',
                    'permission' => 'produksi.view',
                ],
                [
                    'label' => 'Katalog Produk',
                    'route' => 'produksi.produk.index',
                    'active' => 'produksi/produk*',
                    'icon' => 'box',
                    'permission' => 'produksi.view',
                ],
                [
                    'label' => 'Green BoM',
                    'route' => 'produksi.bom.index',
                    'active' => 'produksi/bom*',
                    'icon' => 'leaf',
                    'permission' => 'produksi.view',
                ],
                [
                    'label' => 'Work Order (WO)',
                    'route' => 'produksi.wo.index',
                    'active' => 'produksi/wo*',
                    'icon' => 'play-circle',
                    'permission' => 'produksi.view',
                ],
                [
                    'label' => 'Limbah Produksi',
                    'route' => 'produksi.limbah.index',
                    'active' => 'produksi/limbah*',
                    'icon' => 'recycle',
                    'permission' => 'produksi.view',
                ],
            ],
        ],

        [
            'title' => 'PENJUALAN',
            'items' => [
                [
                    'label' => 'Customer',
                    'route' => 'penjualan.customer.index',
                    'active' => 'penjualan/customer*',
                    'icon' => 'user-check',
                    'permission' => 'penjualan.view',
                ],
                [
                    'label' => 'Sales Order (SO)',
                    'route' => 'penjualan.so.index',
                    'active' => 'penjualan/so*',
                    'icon' => 'shopping-bag',
                    'permission' => 'penjualan.view',
                ],
                [
                    'label' => 'Pengiriman & Invoice',
                    'route' => 'penjualan.pengiriman.index',
                    'active' => 'penjualan/pengiriman*',
                    'icon' => 'send',
                    'permission' => 'penjualan.view',
                ],
            ],
        ],

        [
            'title' => 'BURSA LIMBAH & PASPOR',
            'items' => [
                [
                    'label' => 'Listing Bursa Limbah',
                    'route' => 'bursa.listing.index',
                    'active' => 'bursa/listing*',
                    'icon' => 'tag',
                    'permission' => 'bursa.view',
                ],
                [
                    'label' => 'Pesanan Limbah',
                    'route' => 'bursa.order.index',
                    'active' => 'bursa/order*',
                    'icon' => 'inbox',
                    'permission' => 'bursa.view',
                ],
                [
                    'label' => 'Eco-Passport Produk',
                    'route' => 'passport.index',
                    'active' => 'passport*',
                    'icon' => 'shield-check',
                    'permission' => 'passport.view',
                ],
            ],
        ],

        [
            'title' => 'PENGATURAN SISTEM',
            'items' => [
                [
                    'label' => 'Kelola Pengguna & Peran',
                    'route' => 'admin.users.index',
                    'active' => 'admin/users*',
                    'icon' => 'shield',
                    'permission' => 'users.manage',
                ],
            ],
        ],
    ],
];
