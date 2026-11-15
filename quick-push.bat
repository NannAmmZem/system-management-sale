@echo off
:: Quick Push Batch File
:: Double-click this file to push your changes to GitHub

echo =====================================
echo   SMS Quick Push
echo =====================================
echo.

cd /d "c:\laragon\www\Sale Management System\sms-app"

echo Checking status...
git status

echo.
set /p message="Enter commit message: "

if "%message%"=="" (
    set message=Update at %date% %time%
)

echo.
echo Staging changes...
git add .

echo.
echo Committing...
git commit -m "%message%"

echo.
echo Pushing to GitHub...
git push origin main

if %errorlevel% neq 0 (
    echo.
    echo [ERROR] Failed to push changes!
    pause
    exit /b 1
)

echo.
echo =====================================
echo   Successfully Pushed!
echo =====================================
echo.
pause
