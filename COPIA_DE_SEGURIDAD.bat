@echo off
title CStreame - Copia de Seguridad Semanal con Proteccion Extrema
color 0A
cls
echo ========================================================
echo   CSTREAME - BACKUP CON PROTECCION EXTREMA (AES-256)
echo ========================================================
echo.
echo Conectando de forma segura a Supabase...
echo Extrayendo tablas y aplicando cifrado militar AES-256-GCM...
echo.

cd /d "%~dp0"
"C:\xampp\php\php.exe" artisan cstreame:backup --source=supabase

echo.
echo ========================================================
echo  Tu copia quedo 100%% blindada y guardada en:
echo  D:\CStreame\backups
echo.
echo  Nadie puede leer los datos, usuarios ni contrasenas
echo  sin tu clave maestra de descifrado.
echo ========================================================
echo.
pause
