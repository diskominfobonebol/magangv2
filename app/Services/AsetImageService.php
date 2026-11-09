<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AsetImageService
{
    /**
     * Proses upload foto aset:
     * - Re-encode lewat GD untuk membuang EXIF metadata & memastikan berkas benar-benar gambar
     * - Resize ke maksimal 800px pada sisi terpanjang untuk foto utama
     * - Generate thumbnail 150px untuk tabel web dan laporan PDF
     * - Simpan dengan nama file acak di storage/app/public/aset
     *
     * @return array [ 'foto' => 'aset/nama_acak.jpg', 'thumb' => 'aset/thumb_nama_acak.jpg' ]
     */
    public function uploadAndProcess(UploadedFile $file): array
    {
        $folder = config('aset.foto.storage_folder', 'aset');
        $disk = config('aset.foto.storage_disk', 'public');
        $maxDimension = config('aset.foto.max_dimension', 800);
        $thumbSize = config('aset.foto.thumb_size', 150);
        $quality = config('aset.foto.quality', 85);

        // Buat resource gambar via GD dari file upload
        $srcImage = $this->createGdImage($file);
        if (!$srcImage) {
            throw new \InvalidArgumentException('Format berkas gambar tidak valid atau rusak.');
        }

        $origWidth = imagesx($srcImage);
        $origHeight = imagesy($srcImage);

        // 1. Generate nama file unik & acak
        $randomName = 'aset_' . Str::random(24) . '_' . time() . '.jpg';
        $thumbName  = 'thumb_' . $randomName;

        $targetDir = Storage::disk($disk)->path($folder);
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        $mainPath  = $targetDir . DIRECTORY_SEPARATOR . $randomName;
        $thumbPath = $targetDir . DIRECTORY_SEPARATOR . $thumbName;

        // 2. Resize & Simpan Foto Utama (Maks. 800px sisi terpanjang)
        $mainImage = $this->resizeGdImage($srcImage, $origWidth, $origHeight, $maxDimension);
        imagejpeg($mainImage, $mainPath, $quality);
        imagedestroy($mainImage);

        // 3. Resize & Simpan Thumbnail (Maks. 150px sisi terpanjang)
        $thumbImage = $this->resizeGdImage($srcImage, $origWidth, $origHeight, $thumbSize);
        imagejpeg($thumbImage, $thumbPath, $quality);
        imagedestroy($thumbImage);

        imagedestroy($srcImage);

        return [
            'foto'  => $folder . '/' . $randomName,
            'thumb' => $folder . '/' . $thumbName,
        ];
    }

    /**
     * Hapus berkas foto utama beserta thumbnail terkait dari storage
     */
    public function delete(?string $relativeFotoPath): void
    {
        if (empty($relativeFotoPath)) {
            return;
        }

        $disk = config('aset.foto.storage_disk', 'public');

        // Hapus foto utama jika ada
        if (Storage::disk($disk)->exists($relativeFotoPath)) {
            Storage::disk($disk)->delete($relativeFotoPath);
        }

        // Hapus thumbnail jika ada
        $thumbPath = $this->getThumbPath($relativeFotoPath);
        if ($thumbPath && Storage::disk($disk)->exists($thumbPath)) {
            Storage::disk($disk)->delete($thumbPath);
        }
    }

    /**
     * Dapatkan path relatif thumbnail dari path foto utama
     */
    public function getThumbPath(?string $relativeFotoPath): ?string
    {
        if (empty($relativeFotoPath)) {
            return null;
        }

        $dir = dirname($relativeFotoPath);
        $base = basename($relativeFotoPath);
        $thumbBase = 'thumb_' . $base;

        return ($dir === '.' || empty($dir)) ? $thumbBase : $dir . '/' . $thumbBase;
    }

    /**
     * Baca berkas upload menjadi resource GD sesuai tipe mime
     */
    protected function createGdImage(UploadedFile $file)
    {
        $realPath = $file->getRealPath();
        $mime = $file->getMimeType();

        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                return @imagecreatefromjpeg($realPath);
            case 'image/png':
                return @imagecreatefrompng($realPath);
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    return @imagecreatefromwebp($realPath);
                }
                return null;
            default:
                // Coba fallback pembacaan umum GD
                $content = @file_get_contents($realPath);
                return $content ? @imagecreatefromstring($content) : null;
        }
    }

    /**
     * Resize gambar mempertahankan rasio aspek
     */
    protected function resizeGdImage($srcImage, int $origWidth, int $origHeight, int $maxSide)
    {
        if ($origWidth <= $maxSide && $origHeight <= $maxSide) {
            $newWidth = $origWidth;
            $newHeight = $origHeight;
        } else {
            if ($origWidth >= $origHeight) {
                $newWidth = $maxSide;
                $newHeight = (int)round(($origHeight / $origWidth) * $maxSide);
            } else {
                $newHeight = $maxSide;
                $newWidth = (int)round(($origWidth / $origHeight) * $maxSide);
            }
        }

        $newWidth = max(1, $newWidth);
        $newHeight = max(1, $newHeight);

        $dstImage = imagecreatetruecolor($newWidth, $newHeight);

        // Isi latar belakang dengan warna putih bersih (berguna untuk PNG transparan saat convert ke JPG)
        $white = imagecolorallocate($dstImage, 255, 255, 255);
        imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $white);

        imagecopyresampled(
            $dstImage,
            $srcImage,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $origWidth, $origHeight
        );

        return $dstImage;
    }
}

