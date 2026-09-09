Halo! Saya siap membantu Anda sebagai Senior Full-Stack Developer. Membangun Sistem Arsip Surat Menyurat dan Notifikasi Kenaikan Pangkat/KGB untuk instansi pemerintahan (Dinas Kominfo Bone Bolango) adalah proyek yang sangat menarik.

Dengan menggunakan **Laravel (LTS)** dan **Tailwind CSS**, kita akan membangun sistem yang aman, terstruktur (mematuhi kaidah *clean code* dan *MVC*), serta mudah di-maintain. Saya akan memandu Anda step-by-step, tidak akan *dump* semua kode sekaligus agar kita bisa memastikan fondasi database-nya kuat terlebih dahulu.

Sebelum kita masuk ke tahap penulisan kode (Migration, Model, dsb.), mari kita selesaikan tahap **Analisis Requirement & Desain Database**.

---

### 1. KLARIFIKASI & ASUMSI (Mohon Konfirmasinya)

Berdasarkan *requirement* yang Anda berikan, ada beberapa hal yang perlu saya konfirmasi dan asumsikan:

1. **Poin 5.1 (Nama di Form Surat):**
* **Asumsi Saya:** Karena ini form SPT/SPPD, "Nama" di sini merujuk pada **"Perihal / Nama Kegiatan"** (misal: "Bimbingan Teknis SPBE ke Jakarta"). Untuk "Nama pembuat surat" akan otomatis terekam oleh sistem melalui user yang sedang login (`created_by`), dan "Nama-nama yang ditugaskan" akan masuk ke relasi *Personil/Pegawai*.
* *Apakah asumsi ini sesuai dengan workflow di Dinas Kominfo?*


2. **Poin 5.1 (Skema Penomoran Otomatis):**
* Format usulan Anda: `001/555/BONE-BOLANGO/2026`.
* **Asumsi Saya:** Nomor urut (`001`) akan **di-reset kembali ke `001` setiap pergantian tahun** untuk masing-masing jenis surat. String `BONE-BOLANGO` akan saya buat dinamis, diambil dari tabel `system_settings` agar jika ada perubahan nomenklatur, tidak perlu bongkar kode.
* Untuk SPPD, formatnya akan mengikuti kode SPPD, misal: `001/090/BONE-BOLANGO/2026`.
* *Apakah skema reset per tahun dan format SPPD ini disetujui?*


3. **Poin 5.2 (Relasi SPT ↔ SPPD):**
* **Asumsi Saya:** Jika sebuah SPT (dengan 3 personil) dibuatkan SPPD-nya, maka SPPD tersebut berlaku untuk ke-3 personil tersebut. Kita tidak perlu memilih ulang personil untuk SPPD, melainkan sistem akan mewarisi data personil dari SPT induknya.
* *Apakah ini sesuai, atau SPPD harus dibuatkan satu per satu untuk masing-masing personil dalam SPT?* (Biasanya SPPD bersifat per orang, tapi ada juga instansi yang membuat SPPD kolektif).



---

### 2. RANCANGAN SKEMA DATABASE (Tahap 1)

Berikut adalah rancangan tabel (*Migration Overview*) berdasarkan normalisasi data yang baik di Laravel:

**1. Tabel `roles` & `users**`

* `roles` (id, nama) -> *Admin Master, Admin Kasubag, Pegawai*
* `users` (id, name, email, password, role_id, pegawai_id [nullable], is_active, timestamps)

**2. Tabel `pegawai` (Master Data)**

* `id`, `nip` (string, unique), `nama` (string), `pangkat_golongan` (string), `jabatan` (string), `no_wa` (string), `timestamps`

**3. Tabel `system_settings**`

* `id`, `key` (unique), `value` (text), `keterangan`, `timestamps`
* *(Contoh isian: `app_name`, `wa_api_url`, `wa_api_key`, `format_surat_instansi`)*

**4. Tabel `jenis_surat**`

* `id`, `kode` (string, cth: '555', '090'), `nama_jenis` (string), `timestamps`

**5. Tabel `surat` (Modul Arsip)**

* `id`
* `jenis_surat_id` (FK)
* `spt_induk_id` (FK ke `surat.id` - Nullable. Digunakan HANYA jika surat ini adalah SPPD)
* `nomor_surat` (string, unique)
* `perihal` (string - Menjawab poin "Nama")
* `tgl_surat` (date - Untuk fitur **Backdate**)
* `uraian` (text)
* `tujuan` (string)
* `keterangan` (text, nullable)
* `created_by` (FK ke `users.id` - Audit Trail)
* `updated_by` (FK ke `users.id`, nullable)
* `created_at`, `updated_at` (Sistem timestamp asli saat data dientri)

**6. Tabel `surat_pegawai` (Pivot Table untuk Personil Surat)**

* `id`, `surat_id` (FK), `pegawai_id` (FK), `keterangan_tugas` (nullable)

**7. Tabel `kenpa_berkala` (Tracking Siklus)**

* `id`
* `pegawai_id` (FK)
* `jenis` (enum: 'kenpa', 'berkala')
* `tgl_terakhir` (date)
* `tgl_jatuh_tempo` (date - *Computed saat input*)
* `status` (enum: 'berjalan', 'selesai/diperbarui')
* `timestamps`

**8. Tabel `jenis_dokumen**`

* `id`, `kategori` (enum: 'kenpa', 'berkala'), `nama_dokumen` (string), `is_wajib` (boolean), `timestamps`

**9. Tabel `dokumen_pegawai**`

* `id`
* `kenpa_berkala_id` (FK)
* `jenis_dokumen_id` (FK)
* `file_path` (string)
* `status_verifikasi` (enum: 'pending', 'disetujui', 'ditolak')
* `uploaded_at` (timestamp)
* `timestamps`

**10. Tabel `notification_logs**`

* `id`, `pegawai_id` (FK), `jenis` (string - 'H-30 Kenpa', 'H-30 Berkala'), `nomor_tujuan` (string), `pesan` (text), `status_kirim` (enum: 'success', 'failed'), `tanggal_kirim` (timestamp)

---

### 3. LIBRARY & TOOLS TAMBAHAN YANG AKAN DIGUNAKAN

1. **barryvdh/laravel-dompdf:** Untuk *generate* PDF SPT & SPPD. Library ini stabil dan mudah disetup di lingkungan XAMPP tanpa perlu install *binary* tambahan di server.
2. **Laravel Task Scheduling (`php artisan schedule:run`):** Untuk mengecek *jatuh tempo* notifikasi WA setiap hari.
3. **Alpine.js (Opsional):** Hanya untuk *dropdown*, *modal*, atau *alert* UI ringan di Tailwind.

---

### BAGAIMANA SELANJUTNYA?

Silakan Anda **baca dan jawab pertanyaan konfirmasi (Asumsi 1, 2, 3)** di atas.

Jika Anda setuju dengan rancangan database tersebut, saya akan langsung memulai dengan memberikan *source code* tahap 1:

1. Pembuatan Project & Setup `.env`
2. Pembuatan *Migration*, *Model* (lengkap dengan *Relationships*), dan *Seeder* untuk data awal.