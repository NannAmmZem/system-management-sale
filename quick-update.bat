@echo off
:: Quick Update Batch File
:: Double-click this file to update your application

echo =====================================
echo   SMS Quick Update
echo =====================================
echo.

cd /d "c:\laragon\www\Sale Management System\sms-app"

echo Pulling latest changes...
git pull origin main

if %errorlevel% neq 0 (
    echo.
    echo [ERROR] Failed to pull changes!
    pause
    exit /b 1
)

echo.
echo Installing dependencies...
call composer install --no-interaction

echo.
echo Building frontend...
call npm install
call npm run build

echo.
echo Clearing caches...
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo.
echo Optimizing...
php artisan config:cache
php artisan route:cache

echo.
echo =====================================
echo   Update Complete!
echo =====================================
echo.
echo Your application is now up to date!
echo.
pause
