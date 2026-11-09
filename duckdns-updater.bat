@echo off
title SIMPATIK - DuckDNS Dynamic IP Updater
cls
echo =====================================================================
echo           SISTEM INFORMASI MANAJEMEN ASET & MAGANG (SIMPATIK)
echo             DuckDNS Dynamic DNS Auto-Updater Service
echo =====================================================================
echo.
set DOMAIN=simpatik-bonebolango
set TOKEN=MASUKKAN_TOKEN_DUCKDNS_ANDA

if "%TOKEN%"=="MASUKKAN_TOKEN_DUCKDNS_ANDA" (
    echo [PERHATIAN] Silakan edit file ini dan masukkan TOKEN DuckDNS Anda!
    echo Buka duckdns-updater.bat di text editor lalu isi bagian TOKEN=.
    echo.
    pause
    exit /b
)

echo Memulai sinkronisasi IP ke %DOMAIN%.duckdns.org setiap 5 menit...
echo Tekan CTRL+C untuk menghentikan.
echo.

:loop
echo [%date% %time%] Mengirim pembaruan IP ke DuckDNS...
powershell -Command "(New-Object System.Net.WebClient).DownloadString('https://www.duckdns.org/update?domains=%DOMAIN%&token=%TOKEN%&ip=')"
echo.
timeout /t 300 /nobreak > nul
goto loop
