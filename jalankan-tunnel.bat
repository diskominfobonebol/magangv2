@echo off
title SIMPATIK - Cloudflare Tunnel (Akses HP & Publik)
cls
echo =====================================================================
echo           SISTEM INFORMASI MANAJEMEN ASET & MAGANG (SIMPATIK)
echo             Dinas Komunikasi dan Informatika Bone Bolango
echo =====================================================================
echo.
echo Menghubungkan server lokal (Port 8000) ke Cloudflare Tunnel publik...
echo CATATAN:
echo 1. Pastikan server Laravel sudah aktif (php artisan serve).
echo 2. Salin URL publik https://xxxx.trycloudflare.com yang muncul di bawah.
echo 3. Jalankan perintah berikut di terminal:
echo    php artisan qr:regenerate --url=URL_YANG_DISALIN
echo.
echo =====================================================================
echo.

cloudflared.exe tunnel --url http://127.0.0.1:8000

pause
