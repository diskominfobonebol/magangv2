<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsetPeralatanMesin extends Model
{
    use HasFactory;

    protected $table = 'aset_peralatan_mesin';
    protected $primaryKey = 'no_reg_pemda';
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->no_reg_pemda)) {
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
     * URL publik untuk QR Code aset (mendukung jaringan lokal IP komputer)
     */
    public function getQrUrlAttribute()
    {
        $baseUrl = config('app.url');

        // Jika config('app.url') kosong atau bernilai localhost/127.0.0.1, gunakan IP jaringan lokal komputer
        if (empty($baseUrl) || str_contains($baseUrl, 'localhost') || str_contains($baseUrl, '127.0.0.1')) {
            $localIp = gethostbyname(gethostname());
            if ($localIp && $localIp !== '127.0.0.1') {
                $port = 8000;
                if (request() && request()->getPort()) {
                    $port = request()->getPort();
                }
                $baseUrl = 'http://' . $localIp . ($port != 80 ? ':' . $port : '');
            }
        }

        return rtrim($baseUrl, '/') . '/aset/' . urlencode($this->no_reg_pemda);
    }
}
