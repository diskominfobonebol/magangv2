@echo off
title SIMPATIK - Jalankan Server Otomatis LAN & QR Code
cls
echo =====================================================================
echo           SISTEM INFORMASI MANAJEMEN ASET & MAGANG (SIMPATIK)
echo             Dinas Komunikasi dan Informatika Bone Bolango
echo =====================================================================
echo.
echo Menjalankan pendeteksi IP otomatis dan server LAN (0.0.0.0:8000)...
echo.

php artisan serve:lan

pause

