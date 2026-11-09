<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Foto Aset Peralatan & Mesin
    |--------------------------------------------------------------------------
    */
    'foto' => [
        'max_size_kb'    => 2048, // 2MB dalam Kilobyte
        'max_size_mb'    => 2,
        'mimes'          => ['jpeg', 'jpg', 'png', 'webp'],
        'mimes_string'   => 'jpeg,jpg,png,webp',
        'max_dimension'  => 800,  // Resolusi terpanjang foto utama (pixel)
        'thumb_size'     => 150,  // Resolusi terpanjang thumbnail tabel & PDF (pixel)
        'storage_disk'   => 'public',
        'storage_folder' => 'aset',
        'quality'        => 85,
    ],

    /*
    |--------------------------------------------------------------------------
    | Standar Kondisi dan Persentase per Unit Barang
    |--------------------------------------------------------------------------
    | Persentase adalah nilai kondisi tiap unit barang, bukan porsi dari total aset.
    | Standar rentang, warna, arti, dan nilai default:
    | - Baik: 80%–100% (default 100%) | Hijau
    | - Rusak Ringan: 50%–79% (default 65%) | Kuning (latar muda, teks tua)
    | - Rusak Berat: 1%–49% (default 25%) | Merah
    | - Hilang: 0% (default 0%, terkunci) | Abu-abu
    */
    'kondisi_standar' => [
        'Baik' => [
            'min'               => 80,
            'max'               => 100,
            'default'           => 100,
            'rentang_label'     => '80%–100%',
            'rentang_deskripsi' => 'Standar 80%–100%',
            'warna'             => 'Hijau',
            'color'             => '#16A34A', // Hijau
            'bg_light'          => '#DCFCE7',
            'text_dark'         => '#166534',
            'border'            => '#86EFAC',
            'badge_css'         => 'background-color: #dcfce7; color: #166534; border: 1px solid #86efac;',
            'arti'              => 'Berfungsi normal, semua komponen berjalan baik, fisik tanpa cacat berat, siap pakai tanpa perbaikan.',
            'label'             => 'Baik',
            'label_pdf'         => 'Baik',
        ],
        'Rusak Ringan' => [
            'min'               => 50,
            'max'               => 79,
            'default'           => 65,
            'rentang_label'     => '50%–79%',
            'rentang_deskripsi' => 'Standar 50%–79%',
            'warna'             => 'Kuning',
            'color'             => '#EAB308', // Kuning
            'bg_light'          => '#FEF9C3', // Kuning muda
            'text_dark'         => '#854D0E', // Kuning tua agar terbaca & kontras cetak
            'border'            => '#FDE047',
            'badge_css'         => 'background-color: #fef9c3; color: #854d0e; border: 1px solid #fde047;',
            'arti'              => 'Masih bisa menyala dan dipakai, tetapi ada kendala kecil yang perlu servis ringan (contoh: baterai drop, beberapa tombol mati, casing retak sedikit).',
            'label'             => 'Rusak Ringan',
            'label_pdf'         => 'Rusak Ringan',
        ],
        'Rusak Berat' => [
            'min'               => 1,
            'max'               => 49,
            'default'           => 25,
            'rentang_label'     => '1%–49%',
            'rentang_deskripsi' => 'Standar 1%–49%',
            'warna'             => 'Merah',
            'color'             => '#DC2626', // Merah
            'bg_light'          => '#FEE2E2',
            'text_dark'         => '#991B1B',
            'border'            => '#FCA5A5',
            'badge_css'         => 'background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;',
            'arti'              => 'Mati total atau komponen utama rusak, tidak bisa dipakai kecuali diganti total atau hanya diambil sparepart-nya.',
            'label'             => 'Rusak Berat',
            'label_pdf'         => 'Rusak Berat',
        ],
        'Hilang' => [
            'min'               => 0,
            'max'               => 0,
            'default'           => 0,
            'rentang_label'     => '0%',
            'rentang_deskripsi' => 'Standar 0%',
            'warna'             => 'Abu-abu',
            'color'             => '#64748B', // Abu-abu
            'bg_light'          => '#F1F5F9',
            'text_dark'         => '#334155',
            'border'            => '#CBD5E1',
            'badge_css'         => 'background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;',
            'arti'              => 'Fisik barang tidak ada di tempat penyimpanan karena hilang atau dicuri, nilainya nol.',
            'label'             => 'Hilang',
            'label_pdf'         => 'Belum Ditemukan Keberadaannya',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pemetaan Warna dan Label Kondisi Aset (Kompatibilitas)
    |--------------------------------------------------------------------------
    */
    'kondisi_colors' => [
        'Baik' => [
            'color'      => '#16A34A',
            'bg_light'   => '#DCFCE7',
            'text_dark'  => '#166534',
            'border'     => '#86EFAC',
            'label'      => 'Baik',
            'label_pdf'  => 'Baik',
            'badge_css'  => 'background-color: #dcfce7; color: #166534; border: 1px solid #86efac;',
        ],
        'Rusak Ringan' => [
            'color'      => '#EAB308',
            'bg_light'   => '#FEF9C3',
            'text_dark'  => '#854D0E',
            'border'     => '#FDE047',
            'label'      => 'Rusak Ringan',
            'label_pdf'  => 'Rusak Ringan',
            'badge_css'  => 'background-color: #fef9c3; color: #854d0e; border: 1px solid #fde047;',
        ],
        'Rusak Berat' => [
            'color'      => '#DC2626',
            'bg_light'   => '#FEE2E2',
            'text_dark'  => '#991B1B',
            'border'     => '#FCA5A5',
            'label'      => 'Rusak Berat',
            'label_pdf'  => 'Rusak Berat',
            'badge_css'  => 'background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;',
        ],
        'Hilang' => [
            'color'      => '#64748B',
            'bg_light'   => '#F1F5F9',
            'text_dark'  => '#334155',
            'border'     => '#CBD5E1',
            'label'      => 'Hilang',
            'label_pdf'  => 'Belum Ditemukan Keberadaannya',
            'badge_css'  => 'background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;',
        ],
    ],
];
