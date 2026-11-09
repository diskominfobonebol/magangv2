@echo off
title SIMPATIK - Cloudflare Named Tunnel (Domain Tetap)
cls
echo =====================================================================
echo           SISTEM INFORMASI MANAJEMEN ASET & MAGANG (SIMPATIK)
echo             Dinas Komunikasi dan Informatika Bone Bolango
echo =====================================================================
echo.
echo Menghubungkan server SIMPATIK ke domain:
echo https://simpatik.bonebolangokab.go.id
echo.
echo Pastikan:
echo 1. Server Laravel sudah aktif (php artisan serve --port=8000)
echo 2. File config.yml sudah dikonfigurasi dengan Tunnel ID yang benar.
echo =====================================================================
echo.

if exist "config.yml" (
    .\cloudflared.exe --config config.yml tunnel run simpatik-aset
) else (
    .\cloudflared.exe tunnel run simpatik-aset
)

pause
