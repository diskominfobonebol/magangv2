<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsetPeralatanMesin extends Model
{
    use HasFactory;

    protected $table = 'aset_peralatan_mesin';
    protected $primaryKey = 'id';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->no_reg_pemda) && empty($model->no_reg_kominfo)) {
                $year = date('Y');
                $prefix = "REG-{$year}-";

                $lastAsset = static::where('no_reg_pemda', 'LIKE', "{$prefix}%")
                    ->orderByRaw("CAST(SUBSTRING_INDEX(no_reg_pemda, '-', -1) AS UNSIGNED) DESC")
                    ->first();

                if ($lastAsset && preg_match('/-(\d+)$/', $lastAsset->no_reg_pemda, $matches)) {
                    $nextNumber = ((int) $matches[1]) + 1;
                } else {
                    $nextNumber = 1;
                }

                $model->no_reg_pemda = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            }
        });
    }

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
        'keterangan_lokasi_unit',
        'no_sk_bast',
        'tgl_mulai_pinjam',
        'tgl_selesai_pinjam',
    ];

    protected $casts = [
        'tgl_mulai_pinjam'   => 'date',
        'tgl_selesai_pinjam' => 'date',
    ];

    /**
     * Dapatkan URL publik yang dapat diakses dari jaringan LAN (HP/Tablet) untuk scan QR Code
     */
    public function getPublicUrlAttribute(): string
    {
        $identifier = $this->no_reg_pemda ?: ($this->no_reg_kominfo ?: $this->id);
        return static::generatePublicUrl((string)$identifier);
    }

    /**
     * Alias kompatibilitas
     */
    public function getQrUrlAttribute(): string
    {
        return $this->public_url;
    }

    /**
     * Helper pembentuk URL publik aset yang ramah jaringan lokal (LAN/WiFi)
     */
    public static function generatePublicUrl(?string $noReg): string
    {
        if (empty($noReg)) {
            return url('/aset');
        }

        $encodedId = urlencode($noReg);
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
}
