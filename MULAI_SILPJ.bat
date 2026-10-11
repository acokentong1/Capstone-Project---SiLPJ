@echo off
setlocal DisableDelayedExpansion
title SiLPJ - Aplikasi Lokal
cd /d "%~dp0"
if defined SILPJ_PHP goto run
where php >nul 2>nul
if not errorlevel 1 (
    set "SILPJ_PHP=php"
    goto run
)
if exist "C:\xampp\php\php.exe" (
    set "SILPJ_PHP=C:\xampp\php\php.exe"
    goto run
)
echo PHP belum ditemukan. Buka terminal Laragon atau tambahkan PHP ke PATH.
echo Alternatif: atur variabel SILPJ_PHP ke lokasi lengkap php.exe.
pause
exit /b 1
:run
"%SILPJ_PHP%" "%~dp0scripts\start-local.php"
set "SILPJ_EXIT=%ERRORLEVEL%"
if not "%SILPJ_EXIT%"=="0" pause
exit /b %SILPJ_EXIT%
