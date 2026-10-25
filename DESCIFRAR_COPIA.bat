@echo off
title CStreame - Descifrador de Copias Protegidas
color 0B
cls
echo ========================================================
echo       CSTREAME - DESCIFRADOR DE COPIA DE SEGURIDAD
echo ========================================================
echo.
echo Descifrando la copia mas reciente con tu clave maestra...
echo Verificando autenticidad y anti-manipulacion (Tag GCM)...
echo.

cd /d "%~dp0"
"C:\xampp\php\php.exe" artisan cstreame:decrypt

echo.
echo ========================================================
echo  Si la clave fue correcta, los archivos legibles
echo  quedaron en la carpeta:
echo  D:\CStreame\backups\descifrados
echo.
echo  Recuerda borrar esa carpeta cuando termines de
echo  revisar para mantener la seguridad extrema.
echo ========================================================
echo.
pause
