# Simple file transfer script for Windows PowerShell
# Run this from your project directory

$ErrorActionPreference = "Stop"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Transferring SMS App to Debian Server" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if we're in the right directory
if (-not (Test-Path "composer.json")) {
    Write-Host "Error: Not in project directory!" -ForegroundColor Red
    Write-Host "Please run this from: C:\laragon\www\Sale Management System\sms-app" -ForegroundColor Yellow
    exit 1
}

$serverUser = "nann"
$serverIP = "192.168.233.100"

Write-Host "Creating archive (this may take a moment)..." -ForegroundColor Yellow

# Create the archive
tar --exclude='node_modules' --exclude='vendor' --exclude='.git' --exclude='storage/logs/*' --exclude='storage/framework/cache/*' --exclude='storage/framework/sessions/*' --exclude='storage/framework/views/*' --exclude='public/build' --exclude='.env' -czf sms-app.tar.gz .

if (Test-Path "sms-app.tar.gz") {
    $fileSize = (Get-Item "sms-app.tar.gz").Length / 1MB
    Write-Host "Archive created: $([math]::Round($fileSize, 2)) MB" -ForegroundColor Green
    
    Write-Host ""
    Write-Host "Transferring to server..." -ForegroundColor Yellow
    
    # Transfer the archive
    scp sms-app.tar.gz "${serverUser}@${serverIP}:~/sms-app.tar.gz"
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "Transfer complete!" -ForegroundColor Green
        
        # Clean up local archive
        Remove-Item sms-app.tar.gz
        
        Write-Host ""
        Write-Host "Now run this on your Debian server:" -ForegroundColor Cyan
        Write-Host "  ~/fix-deployment.sh" -ForegroundColor White
        Write-Host ""
        Write-Host "Or run everything automatically:" -ForegroundColor Cyan
        Write-Host "  ssh ${serverUser}@${serverIP} '~/fix-deployment.sh'" -ForegroundColor White
        Write-Host ""
    } else {
        Write-Host "Transfer failed!" -ForegroundColor Red
        exit 1
    }
} else {
    Write-Host "Failed to create archive!" -ForegroundColor Red
    exit 1
}
