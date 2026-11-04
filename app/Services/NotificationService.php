<?php

namespace App\Services;

use App\Models\User;
use App\Models\Pegawai;
use App\Models\Surat;
use App\Models\SuratTelaah;
use App\Models\KenpaBerkala;
use App\Models\DokumenPegawai;
use App\Notifications\PenugasanSuratNotification;
use App\Notifications\BerkasPegawaiNotification;
use App\Notifications\StatusBerkasNotification;
use App\Notifications\PengingatJatuhTempoNotification;
use App\Notifications\SecurityAlertNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Cek apakah notifikasi dengan dedup_key tertentu sudah ada untuk user.
     */
    public static function hasDuplicateKey(int $userId, string $dedupKey, ?int $withinMinutes = null): bool
    {
        $query = DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $userId)
            ->whereJsonContains('data->dedup_key', $dedupKey);

        if ($withinMinutes !== null) {
            $query->where('created_at', '>=', Carbon::now()->subMinutes($withinMinutes));
        }

        return $query->exists();
    }

    /**
     * Kirim notifikasi penugasan surat ke pegawai (khusus saat surat Terbit).
     */
    public static function notifyPenugasanSurat($surat, array $pegawaiIds, string $suratType = 'spt'): void
    {
        try {
            // Khusus model Surat biasa, pastikan status Terbit (bukan Draft)
            if ($surat instanceof Surat && ($surat->status ?? '') === 'Draft') {
                return;
            }

            $pegawaiIds = array_values(array_unique(array_filter($pegawaiIds)));
            if (empty($pegawaiIds)) {
                return;
            }

            $pegawais = Pegawai::whereIn('id', $pegawaiIds)
                ->whereNotNull('user_id')
                ->with('user')
                ->get();

            $nomorSurat = $surat->nomor_surat ?? $surat->nomor_telaah ?? '-';
            $perihal = $surat->uraian ?? $surat->tujuan ?? 'Tugas Kedinasan';

            $typeLabel = match (strtolower($suratType)) {
                'sppd' => 'SPPD',
                'telaah' => 'Surat Telaah',
                default => 'Surat Perintah Tugas (SPT)',
            };

            foreach ($pegawais as $pegawai) {
                if (!$pegawai->user || !$pegawai->user->is_active) {
                    continue;
                }

                $dedupKey = "penugasan_{$suratType}_{$surat->id}_{$pegawai->id}";
                if (self::hasDuplicateKey($pegawai->user->id, $dedupKey)) {
                    continue;
                }

                $pegawai->user->notify(new PenugasanSuratNotification([
                    'title' => "Penugasan {$typeLabel} Baru",
                    'message' => "Anda ditugaskan pada {$typeLabel} No. {$nomorSurat} perihal {$perihal}.",
                    'url' => '/dashboard/pegawai',
                    'icon' => 'file-text',
                    'dedup_key' => $dedupKey,
                ]));
            }
        } catch (\Throwable $e) {
            report($e);
            Log::warning('NotificationService::notifyPenugasanSurat error: ' . $e->getMessage());
        }
    }

    /**
     * Kirim notifikasi ke Admin Kasubag saat pegawai mengunggah berkas kenaikan pangkat / berkala.
     */
    public static function notifyBerkasDiunggah(Pegawai $pegawai, KenpaBerkala $kenpa, string $namaDokumen): void
    {
        try {
            $kasubags = User::where('role_id', 2)
                ->where('is_active', true)
                ->get();

            if ($kasubags->isEmpty()) {
                return;
            }

            $dedupKey = "upload_berkas_{$pegawai->id}_{$kenpa->id}_" . Carbon::now()->format('YmdHi');

            foreach ($kasubags as $kasubag) {
                if (self::hasDuplicateKey($kasubag->id, $dedupKey)) {
                    continue;
                }

                $kasubag->notify(new BerkasPegawaiNotification([
                    'title' => 'Berkas Pegawai Diunggah',
                    'message' => "Pegawai {$pegawai->nama} telah mengunggah berkas {$namaDokumen} ({$kenpa->jenis}).",
                    'url' => route('kenaikan-pangkat.show', $pegawai->id),
                    'icon' => 'upload-cloud',
                    'dedup_key' => $dedupKey,
                ]));
            }
        } catch (\Throwable $e) {
            report($e);
            Log::warning('NotificationService::notifyBerkasDiunggah error: ' . $e->getMessage());
        }
    }

    /**
     * Kirim notifikasi ke pegawai saat status verifikasi berkas diperbarui (disetujui / ditolak).
     */
    public static function notifyStatusBerkas(DokumenPegawai $dokumen, string $status, ?string $keteranganAdmin = null): void
    {
        try {
            $kenpa = $dokumen->kenpaBerkala;
            $pegawai = $kenpa?->pegawai;

            if (!$pegawai || !$pegawai->user_id) {
                return;
            }

            $user = User::find($pegawai->user_id);
            if (!$user || !$user->is_active) {
                return;
            }

            $namaDokumen = $dokumen->jenisDokumen->nama_dokumen ?? 'Persyaratan';
            $statusText = ($status === 'disetujui') ? 'disetujui' : 'ditolak';
            $titleStatus = ($status === 'disetujui') ? 'Disetujui' : 'Ditolak';

            $msg = "Berkas {$namaDokumen} Anda telah {$statusText} oleh Admin Kasubag.";
            if ($keteranganAdmin) {
                $msg .= " Catatan: {$keteranganAdmin}";
            }

            $dedupKey = "status_berkas_{$dokumen->id}_{$status}_" . Carbon::now()->format('YmdHi');
            if (self::hasDuplicateKey($user->id, $dedupKey)) {
                return;
            }

            $user->notify(new StatusBerkasNotification([
                'title' => "Status Berkas {$titleStatus}",
                'message' => $msg,
                'url' => '/dashboard/pegawai',
                'icon' => ($status === 'disetujui') ? 'check-circle' : 'x-circle',
                'dedup_key' => $dedupKey,
            ]));
        } catch (\Throwable $e) {
            report($e);
            Log::warning('NotificationService::notifyStatusBerkas error: ' . $e->getMessage());
        }
    }

    /**
     * Kirim notifikasi peringatan keamanan (lockout percobaan login/2FA gagal berulang) ke Admin Master.
     * Dibatasi 1 notifikasi per identifier target per 15 menit.
     */
    public static function notifySecurityLockout(string $identifier, ?string $action = 'login'): void
    {
        try {
            $masters = User::where('role_id', 1)
                ->where('is_active', true)
                ->get();

            if ($masters->isEmpty()) {
                return;
            }

            $dedupKey = "lockout_{$identifier}";

            foreach ($masters as $master) {
                // Pencegah banjir notifikasi: 1 notifikasi per identifier per 15 menit
                if (self::hasDuplicateKey($master->id, $dedupKey, 15)) {
                    continue;
                }

                $actionLabel = ($action === '2fa') ? 'Verifikasi 2FA' : 'Login';
                $master->notify(new SecurityAlertNotification([
                    'title' => 'Peringatan Keamanan Akun',
                    'message' => "Percobaan {$actionLabel} gagal berulang kali untuk akun '{$identifier}'. Akses sementara ditangguhkan (lockout).",
                    'url' => route('admin.users.index'),
                    'icon' => 'shield-alert',
                    'dedup_key' => $dedupKey,
                ]));
            }
        } catch (\Throwable $e) {
            report($e);
            Log::warning('NotificationService::notifySecurityLockout error: ' . $e->getMessage());
        }
    }

    /**
     * Kirim pengingat jatuh tempo Kenpa/KGB H-30.
     * Mengembalikan array ringkasan: ['kasubag_count' => int, 'pegawai_count' => int]
     */
    public static function notifyPengingatJatuhTempoH30(KenpaBerkala $kenpa, bool $dryRun = false): array
    {
        $result = ['kasubag' => 0, 'pegawai' => 0];

        try {
            $pegawai = $kenpa->pegawai;
            if (!$pegawai) {
                return $result;
            }

            $targetDate = Carbon::parse($kenpa->tgl_jatuh_tempo)->format('Y-m-d');
            $tglFormatted = Carbon::parse($kenpa->tgl_jatuh_tempo)->translatedFormat('d F Y');
            $jenis = $kenpa->jenis ?? 'Kenaikan Pangkat/KGB';

            // 1. Notifikasi untuk Admin Kasubag
            $kasubags = User::where('role_id', 2)->where('is_active', true)->get();
            $dedupKeyKasubag = "h30_kasubag_{$kenpa->id}_{$targetDate}";

            foreach ($kasubags as $kasubag) {
                if (!self::hasDuplicateKey($kasubag->id, $dedupKeyKasubag)) {
                    $result['kasubag']++;
                    if (!$dryRun) {
                        $kasubag->notify(new PengingatJatuhTempoNotification([
                            'title' => 'Pengingat H-30 Kenaikan Pangkat/KGB',
                            'message' => "Pegawai {$pegawai->nama} akan jatuh tempo {$jenis} pada tanggal {$tglFormatted}.",
                            'url' => route('kenaikan-pangkat.show', $pegawai->id),
                            'icon' => 'clock',
                            'dedup_key' => $dedupKeyKasubag,
                        ]));
                    }
                }
            }

            // 2. Notifikasi untuk Pegawai bersangkutan (jika memiliki akun aktif)
            if ($pegawai->user_id) {
                $userPegawai = User::find($pegawai->user_id);
                if ($userPegawai && $userPegawai->is_active) {
                    $dedupKeyPegawai = "h30_pegawai_{$kenpa->id}_{$targetDate}";
                    if (!self::hasDuplicateKey($userPegawai->id, $dedupKeyPegawai)) {
                        $result['pegawai']++;
                        if (!$dryRun) {
                            $userPegawai->notify(new PengingatJatuhTempoNotification([
                                'title' => "Pengingat Jatuh Tempo {$jenis}",
                                'message' => "Masa {$jenis} Anda akan jatuh tempo pada tanggal {$tglFormatted} (30 hari lagi). Silakan persiapkan dan lengkapi berkas persyaratan Anda.",
                                'url' => '/dashboard/pegawai',
                                'icon' => 'clock',
                                'dedup_key' => $dedupKeyPegawai,
                            ]));
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            report($e);
            Log::warning('NotificationService::notifyPengingatJatuhTempoH30 error: ' . $e->getMessage());
        }

        return $result;
    }
}

