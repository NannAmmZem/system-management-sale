@echo off
echo ================================================
echo Setting up access to nannwebsite.kik
echo ================================================
echo.

REM Get WiFi interface name
for /f "tokens=1,2,*" %%a in ('netsh interface show interface ^| findstr /C:"Connected" ^| findstr /C:"Wi-Fi"') do set WIFI_NAME=%%c

if "%WIFI_NAME%"=="" (
    echo Could not find WiFi interface. Trying default name...
    set WIFI_NAME=Wi-Fi
)

echo Setting DNS server to 192.168.233.100...
netsh interface ip set dns name="%WIFI_NAME%" static 192.168.233.100 primary
netsh interface ip add dns name="%WIFI_NAME%" 8.8.8.8 index=2

echo Flushing DNS cache...
ipconfig /flushdns

echo.
echo ================================================
echo Setup complete!
echo You can now access: http://nannwebsite.kik
echo ================================================
echo.
pause
