<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleDriveService
{
    protected ?Client $client = null;
    protected ?Drive $service = null;
    protected ?string $rootFolderId = null;
    protected string $credentialsPath;

    public function __construct()
    {
        $configPath = config('services.google_drive.credentials');
        if (!$configPath || !file_exists($configPath)) {
            // Cek path alternatif
            $altPath = storage_path('app/google-drive-credentials.json');
            $this->credentialsPath = file_exists($altPath) ? $altPath : ($configPath ?: $altPath);
        } else {
            $this->credentialsPath = $configPath;
        }

        $this->rootFolderId = config('services.google_drive.root_folder_id') ?: null;
    }

    /**
     * Inisialisasi Google API Client & Drive Service.
     */
    protected function getService(): Drive
    {
        if ($this->service !== null) {
            return $this->service;
        }

        if (!file_exists($this->credentialsPath)) {
            throw new Exception("File credential Service Account Google Drive tidak ditemukan di: {$this->credentialsPath}. Silakan letakkan file JSON credential di lokasi tersebut.");
        }

        $this->client = new Client();
        $this->client->setAuthConfig($this->credentialsPath);
        $this->client->addScope(Drive::DRIVE);
        $this->client->setAccessType('offline');

        $this->service = new Drive($this->client);

        return $this->service;
    }

    /**
     * Cek dan uji koneksi ke Google Drive.
     */
    public function testConnection(): array
    {
        try {
            $service = $this->getService();
            
            // Coba ambil info Drive atau list 5 file/folder teratas
            $optParams = [
                'pageSize' => 5,
                'fields' => 'nextPageToken, files(id, name, mimeType, trashed)',
                'q' => "trashed = false",
            ];
            
            if ($this->rootFolderId) {
                $optParams['q'] = "'{$this->rootFolderId}' in parents and trashed = false";
            }

            $results = $service->files->listFiles($optParams);
            $fileList = [];
            foreach ($results->getFiles() as $file) {
                $fileList[] = [
                    'id' => $file->getId(),
                    'name' => $file->getName(),
                    'mime_type' => $file->getMimeType(),
                ];
            }

            return [
                'success' => true,
                'message' => 'Koneksi ke Google Drive Service Account berhasil terhubung!',
                'credentials_path' => $this->credentialsPath,
                'root_folder_id' => $this->rootFolderId ?: '(Root My Drive Service Account)',
                'sample_files_count' => count($fileList),
                'sample_files' => $fileList,
            ];
        } catch (Exception $e) {
            Log::error('Google Drive test connection failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke Google Drive: ' . $e->getMessage(),
                'credentials_path' => $this->credentialsPath,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cari folder berdasarkan nama dan parent ID, atau buat baru jika belum ada (Idempotent).
     */
    public function findOrCreateFolder(string $folderName, ?string $parentId = null): string
    {
        $service = $this->getService();
        $sanitizedName = str_replace("'", "\\'", $folderName);

        $query = "mimeType = 'application/vnd.google-apps.folder' and name = '{$sanitizedName}' and trashed = false";
        if ($parentId) {
            $query .= " and '{$parentId}' in parents";
        } elseif ($this->rootFolderId) {
            $query .= " and '{$this->rootFolderId}' in parents";
        }

        $response = $service->files->listFiles([
            'q' => $query,
            'spaces' => 'drive',
            'fields' => 'files(id, name)',
            'pageSize' => 1,
        ]);

        if (count($response->getFiles()) > 0) {
            return $response->getFiles()[0]->getId();
        }

        // Jika belum ada, buat folder baru
        $folderMetadata = new DriveFile([
            'name' => $folderName,
            'mimeType' => 'application/vnd.google-apps.folder',
        ]);

        if ($parentId) {
            $folderMetadata->setParents([$parentId]);
        } elseif ($this->rootFolderId) {
            $folderMetadata->setParents([$this->rootFolderId]);
        }

        $folder = $service->files->create($folderMetadata, [
            'fields' => 'id',
        ]);

        return $folder->id;
    }

    /**
     * Dapatkan ID folder target berdasarkan Tahun dan Jenis Surat (e.g. 2026/SPT atau 2026/SPPD).
     */
    public function getTargetFolderId(string $year, string $jenisSurat = 'SPT'): string
    {
        // 1. Folder Tahun (contoh: '2026')
        $yearFolderId = $this->findOrCreateFolder($year, $this->rootFolderId);

        // 2. Sub-folder Jenis Surat (contoh: 'SPT' atau 'SPPD')
        $cleanJenis = strtoupper(trim($jenisSurat));
        if (!in_array($cleanJenis, ['SPT', 'SPPD'])) {
            $cleanJenis = 'SPT';
        }

        return $this->findOrCreateFolder($cleanJenis, $yearFolderId);
    }

    /**
     * Upload file surat ke Google Drive.
     *
     * @param UploadedFile|string $file Objek UploadedFile atau absolute path ke file lokal
     * @param string $nomorSurat Nomor surat resmi untuk penamaan file
     * @param string $jenisSurat Jenis surat (SPT / SPPD)
     * @param string $year Tahun surat (e.g. 2026)
     * @return array
     */
    public function uploadSuratFile($file, string $nomorSurat, string $jenisSurat = 'SPT', string $year = '2026'): array
    {
        try {
            $service = $this->getService();

            $targetFolderId = $this->getTargetFolderId($year, $jenisSurat);

            // Tentukan mime type, ekstensi, dan konten
            if ($file instanceof UploadedFile) {
                $extension = $file->getClientOriginalExtension() ?: 'pdf';
                $mimeType = $file->getMimeType() ?: 'application/pdf';
                $content = file_get_contents($file->getRealPath());
            } elseif (is_string($file) && file_exists($file)) {
                $extension = pathinfo($file, PATHINFO_EXTENSION) ?: 'pdf';
                $mimeType = mime_content_type($file) ?: 'application/pdf';
                $content = file_get_contents($file);
            } else {
                throw new Exception('File tidak valid atau tidak ditemukan.');
            }

            // Bersihkan nomor surat untuk nama file: ganti '/' dengan '-'
            $cleanNumber = preg_replace('/[\/\\\\]+/', '-', trim($nomorSurat));
            $cleanFileName = "{$cleanNumber}.{$extension}";

            $fileMetadata = new DriveFile([
                'name' => $cleanFileName,
                'parents' => [$targetFolderId],
            ]);

            $createdFile = $service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'fields' => 'id, name, webViewLink, webContentLink',
            ]);

            $fileId = $createdFile->id;

            // Set permission: Publik / Anyone with link can view (Viewer-only)
            try {
                $permission = new Permission([
                    'type' => 'anyone',
                    'role' => 'reader',
                ]);
                $service->permissions->create($fileId, $permission);
            } catch (Exception $permEx) {
                Log::warning("Gagal menyetel permission publik file Drive {$fileId}: " . $permEx->getMessage());
            }

            // Ambil kembali webViewLink terbaru
            $finalFile = $service->files->get($fileId, [
                'fields' => 'id, name, webViewLink, webContentLink',
            ]);

            $webViewLink = $finalFile->webViewLink ?: "https://drive.google.com/file/d/{$fileId}/view";

            return [
                'success' => true,
                'file_id' => $fileId,
                'file_name' => $cleanFileName,
                'web_view_link' => $webViewLink,
                'web_content_link' => $finalFile->webContentLink ?? null,
            ];
        } catch (Exception $e) {
            Log::error("Google Drive Upload Error [{$nomorSurat}]: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'file_id' => null,
                'web_view_link' => null,
            ];
        }
    }

    /**
     * Hapus file di Google Drive berdasarkan File ID (jika ada pergantian berkas).
     */
    public function deleteFile(?string $fileId): bool
    {
        if (empty($fileId)) {
            return false;
        }

        try {
            $service = $this->getService();
            $service->files->delete($fileId);
            return true;
        } catch (Exception $e) {
            Log::warning("Gagal menghapus file lama di Drive ({$fileId}): " . $e->getMessage());
            return false;
        }
    }
}
