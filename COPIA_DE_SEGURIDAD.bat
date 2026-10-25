@echo off
title CStreame - Copia de Seguridad Semanal
color 0A
cls
echo ========================================================
echo       CSTREAME - COPIA DE SEGURIDAD AUTOMATICA
echo ========================================================
echo.
echo Conectando a Supabase y descargando respaldo a tu PC...
echo.

cd /d "%~dp0"
"C:\xampp\php\php.exe" artisan cstreame:backup --source=supabase

echo.
echo ========================================================
echo  Tu copia quedo guardada en la carpeta:
echo  D:\CStreame\backups
echo ========================================================
echo.
pause
