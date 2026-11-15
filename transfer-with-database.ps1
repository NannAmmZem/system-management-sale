# Transfer SMS App with Database to Debian Server
# Run this from your project directory on Windows

$ErrorActionPreference = "Stop"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Transfer SMS App + Database to Debian" -ForegroundColor Cyan
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

# Check if database exists
$dbPath = "database\database.sqlite"
if (Test-Path $dbPath) {
    $dbSize = (Get-Item $dbPath).Length / 1KB
    Write-Host "Found database: $([math]::Round($dbSize, 2)) KB" -ForegroundColor Green
} else {
    Write-Host "Warning: No SQLite database found at $dbPath" -ForegroundColor Yellow
    Write-Host "Will create a new database on the server." -ForegroundColor Yellow
    Write-Host ""
}

Write-Host "Creating archive (including database)..." -ForegroundColor Yellow

# Create archive INCLUDING the database
tar --exclude='node_modules' --exclude='vendor' --exclude='.git' --exclude='storage/logs/*' --exclude='storage/framework/cache/*' --exclude='storage/framework/sessions/*' --exclude='storage/framework/views/*' --exclude='public/build' -czf sms-app.tar.gz .

if (Test-Path "sms-app.tar.gz") {
    $fileSize = (Get-Item "sms-app.tar.gz").Length / 1MB
    Write-Host "Archive created: $([math]::Round($fileSize, 2)) MB" -ForegroundColor Green
    
    Write-Host ""
    Write-Host "Transferring to server (this may take a moment)..." -ForegroundColor Yellow
    
    # Transfer the archive
    scp sms-app.tar.gz "${serverUser}@${serverIP}:~/sms-app.tar.gz"
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "Transfer complete!" -ForegroundColor Green
        
        # Clean up local archive
        Remove-Item sms-app.tar.gz
        
        Write-Host ""
        Write-Host "Files and database transferred successfully!" -ForegroundColor Green
        Write-Host ""
        Write-Host "Now run this on your Debian server:" -ForegroundColor Cyan
        Write-Host "  ~/fix-deployment.sh" -ForegroundColor White
        Write-Host ""
        Write-Host "The script will:" -ForegroundColor Yellow
        Write-Host "  - Extract all files including your database" -ForegroundColor White
        Write-Host "  - Set up the environment" -ForegroundColor White
        Write-Host "  - Configure permissions" -ForegroundColor White
        Write-Host "  - Start the web server" -ForegroundColor White
        Write-Host ""
    } else {
        Write-Host "Transfer failed!" -ForegroundColor Red
        exit 1
    }
} else {
    Write-Host "Failed to create archive!" -ForegroundColor Red
    exit 1
}
