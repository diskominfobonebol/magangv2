<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsetPeralatanMesin extends Model
{
    use HasFactory;

    protected $table = 'aset_peralatan_mesin';
    protected $primaryKey = 'id';

    protected $fillable = [
        'no_reg_pemda',
        'no_reg_kominfo',
        'penanggung_jawab',
        'jenis_barang',
        'merek_tipe',
        'tahun',
        'harga_perolehan',
        'no_rangka_seri',
        'no_mesin',
        'no_polisi',
        'no_bpkb',
        'kondisi',
        'persentase_kondisi',
        'keterangan_lokasi_unit',
        'foto',
        'no_sk_bast',
        'tgl_mulai_pinjam',
        'tgl_selesai_pinjam',
    ];

    protected static function booted()
    {
        static::deleting(function ($aset) {
            if (!empty($aset->foto)) {
                app(\App\Services\AsetImageService::class)->delete($aset->foto);
            }
        });
    }

    /**
     * URL foto aset ukuran utama (800px) - menggunakan path relatif agar aman di host/tunnel mana pun
     */
    public function getFotoUrlAttribute(): ?string
    {
        if (empty($this->foto)) {
            return null;
        }

        return '/storage/' . ltrim($this->foto, '/');
    }

    /**
     * URL thumbnail foto aset (150px) untuk tabel web - menggunakan path relatif
     */
    public function getThumbUrlAttribute(): ?string
    {
        if (empty($this->foto)) {
            return null;
        }

        $service = app(\App\Services\AsetImageService::class);
        $thumbPath = $service->getThumbPath($this->foto);

        return '/storage/' . ltrim($thumbPath, '/');
    }

    /**
     * Dimensi tampil thumbnail proporsional sesuai rasio asli (maks 110x90 px)
     * Digunakan untuk atribut width dan height eksplisit pada DomPDF dan tabel web
     */
    public function getThumbDimensionsAttribute(): ?array
    {
        if (empty($this->foto)) {
            return null;
        }

        $service = app(\App\Services\AsetImageService::class);
        $thumbPath = $service->getThumbPath($this->foto);
        $disk = config('aset.foto.storage_disk', 'public');

        $fullPath = null;
        if ($thumbPath && \Illuminate\Support\Facades\Storage::disk($disk)->exists($thumbPath)) {
            $fullPath = \Illuminate\Support\Facades\Storage::disk($disk)->path($thumbPath);
        } elseif (\Illuminate\Support\Facades\Storage::disk($disk)->exists($this->foto)) {
            $fullPath = \Illuminate\Support\Facades\Storage::disk($disk)->path($this->foto);
        }

        if (!$fullPath || !file_exists($fullPath)) {
            return null;
        }

        $size = @getimagesize($fullPath);
        if (!$size || $size[0] <= 0 || $size[1] <= 0) {
            return ['width' => 90, 'height' => 70];
        }

        $origW = $size[0];
        $origH = $size[1];

        $maxW = 110;
        $maxH = 90;

        // Skala proporsional agar muat dalam batas maksimal tanpa terdistorsi
        $ratio = min($maxW / $origW, $maxH / $origH, 1.0);
        $calcW = max(1, (int) round($origW * $ratio));
        $calcH = max(1, (int) round($origH * $ratio));

        return [
            'width'  => $calcW,
            'height' => $calcH,
        ];
    }

    /**
     * Data URI thumbnail lokal base64 untuk disematkan di DomPDF tanpa HTTP request eksternal
     */
    public function getThumbDataUriAttribute(): ?string
    {
        if (empty($this->foto)) {
            return null;
        }

        $service = app(\App\Services\AsetImageService::class);
        $thumbPath = $service->getThumbPath($this->foto);

        $disk = config('aset.foto.storage_disk', 'public');
        if (\Illuminate\Support\Facades\Storage::disk($disk)->exists($thumbPath)) {
            $fullPath = \Illuminate\Support\Facades\Storage::disk($disk)->path($thumbPath);
            $content = @file_get_contents($fullPath);
            if ($content) {
                return 'data:image/jpeg;base64,' . base64_encode($content);
            }
        } elseif (\Illuminate\Support\Facades\Storage::disk($disk)->exists($this->foto)) {
            $fullPath = \Illuminate\Support\Facades\Storage::disk($disk)->path($this->foto);
            $content = @file_get_contents($fullPath);
            if ($content) {
                return 'data:image/jpeg;base64,' . base64_encode($content);
            }
        }

        return null;
    }

    protected $casts = [
        'tgl_mulai_pinjam'   => 'date',
        'tgl_selesai_pinjam' => 'date',
        'persentase_kondisi' => 'integer',
    ];

    /**
     * Dapatkan konfigurasi standar untuk kondisi aset saat ini
     */
    public function getKondisiConfigAttribute(): array
    {
        $all = config('aset.kondisi_standar', []);
        return $all[$this->kondisi] ?? [
            'color'      => '#64748B',
            'bg_light'   => '#F1F5F9',
            'text_dark'  => '#334155',
            'border'     => '#CBD5E1',
            'badge_css'  => 'background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;',
            'label'      => $this->kondisi,
            'label_pdf'  => $this->kondisi,
            'rentang_label' => '-',
        ];
    }

    /**
     * Dapatkan URL publik yang dapat diakses dari jaringan LAN (HP/Tablet) untuk scan QR Code
     */
    public function getPublicUrlAttribute(): string
    {
        $identifier = $this->no_reg_pemda ?: ($this->no_reg_kominfo ?: $this->id);
        return static::generatePublicUrl((string)$identifier);
    }

    /**
     * Helper pembentuk URL publik aset yang ramah jaringan lokal (LAN/WiFi)
     */
    public static function generatePublicUrl(?string $noRegPemda): string
    {
        if (empty($noRegPemda)) {
            return url('/aset');
        }

        $encodedId = urlencode($noRegPemda);
        $request = request();
        $host = $request ? $request->getHost() : null;

        // 1. Jika request browser berasal dari IP jaringan eksternal / domain nyata (bukan localhost / loopback)
        if ($host && $host !== '127.0.0.1' && $host !== 'localhost' && $host !== '::1') {
            return url('/aset/' . $encodedId);
        }

        // 2. Jika konfigurasi APP_URL di .env menggunakan IP LAN atau Domain (bukan localhost / 127.0.0.1)
        $appUrl = rtrim(config('app.url') ?? '', '/');
        if (!empty($appUrl) && !str_contains($appUrl, '127.0.0.1') && !str_contains($appUrl, 'localhost')) {
            return $appUrl . '/aset/' . $encodedId;
        }

        // 3. Fallback: Otomatis mendeteksi IP lokal mesin (WiFi/LAN) agar dapat dibuka perangkat HP
        $lanIp = @gethostbyname(gethostname());
        if ($lanIp && $lanIp !== '127.0.0.1' && !str_starts_with($lanIp, '169.254.')) {
            $port = $request ? $request->getPort() : 8000;
            $portStr = ($port && $port != 80 && $port != 443) ? ":{$port}" : '';
            $scheme = ($request && $request->getScheme()) ? $request->getScheme() : 'http';
            $basePath = $request ? $request->getBasePath() : '';
            return "{$scheme}://{$lanIp}{$portStr}{$basePath}/aset/{$encodedId}";
        }

        return url('/aset/' . $encodedId);
    }

    /**
     * Relasi ke seluruh Berita Acara (BAST / Penerimaan Barang) terkait aset ini
     */
    public function beritaAcaras()
    {
        return $this->hasMany(BeritaAcara::class, 'aset_id')->orderBy('tanggal', 'desc');
    }
}