@echo off
echo ========================================
echo Share Laragon Project on Local Network
echo ========================================
echo.

REM Get your local IP address
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /c:"IPv4 Address"') do (
    set IP=%%a
    goto :found
)

:found
set IP=%IP:~1%
echo Your IP Address: %IP%
echo.

echo Your project will be accessible at:
echo   http://%IP%/
echo   or
echo   http://%IP%:80/
echo.

echo ========================================
echo Configuring Windows Firewall...
echo ========================================

REM Allow Apache through firewall
netsh advfirewall firewall add rule name="Laragon Apache HTTP" dir=in action=allow protocol=TCP localport=80
netsh advfirewall firewall add rule name="Laragon Apache HTTPS" dir=in action=allow protocol=TCP localport=443

echo.
echo ========================================
echo Configuration Complete!
echo ========================================
echo.
echo Share this with your friends:
echo   http://%IP%/
echo.
echo Make sure:
echo 1. Laragon is running
echo 2. Your project is started
echo 3. Other PCs are on the same WiFi
echo.
pause
