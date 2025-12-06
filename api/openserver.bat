@echo off
setlocal EnableDelayedExpansion
title Portable Laravel Server Manager (v2.0)
color 0B

:: --------------------------------------------
:: CONFIGURATION
:: --------------------------------------------
:: Use current folder as project root
cd /d "%~dp0"

:MENU
cls
echo ===============================================
echo   PORTABLE SERVER MANAGER (PowerShell Edition)
echo   Project: %CD%
echo ===============================================
echo.
echo   [1] START 7 Servers (Ports 8000-8006)
echo   [2] STOP 7 Servers (Force Kill Ports)
echo   [3] Exit
echo.
set /p choice="Select option: "

if "%choice%"=="1" goto START_SERVERS
if "%choice%"=="2" goto STOP_SERVERS
if "%choice%"=="3" exit
goto MENU

:START_SERVERS
cls
if not exist "artisan" (
    color 0C
    echo ERROR: 'artisan' file not found!
    echo Please move this file to your Laravel project folder.
    pause
    goto MENU
)

echo Starting servers...
for /L %%i in (8000,1,8006) do (
    echo Launching Port %%i...
    start "Laravel Port %%i" /min php artisan serve --port=%%i
)
echo.
echo Servers are running in the background!
pause
goto MENU

:STOP_SERVERS
cls
echo Stopping Ports 8000 to 8006...
echo -----------------------------------

:: This single line uses PowerShell to cleanly find and kill the ports
powershell -Command "8000..8006 | ForEach-Object { $p = Get-NetTCPConnection -LocalPort $_ -ErrorAction SilentlyContinue; if ($p) { Write-Host 'Killing Port' $_; Stop-Process -Id $p.OwningProcess -Force } }"

echo.
echo -----------------------------------
echo Cleanup complete.
pause
goto MENU