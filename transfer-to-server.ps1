# PowerShell script to transfer files to Debian server
# Run this on your Windows machine

$serverUser = "nann"
$serverIP = "192.168.233.100"
$appName = "sms-app"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "SMS App File Transfer Script" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if we're in the right directory
if (-not (Test-Path "composer.json")) {
    Write-Host "Error: composer.json not found. Please run this script from the project root." -ForegroundColor Red
    exit 1
}

Write-Host "Creating deployment archive..." -ForegroundColor Yellow

# Create archive excluding unnecessary files
$excludeFiles = @(
    "node_modules",
    "vendor",
    ".git",
    "storage/logs/*",
    "storage/framework/cache/*",
    "storage/framework/sessions/*",
    "storage/framework/views/*",
    "public/build",
    ".env",
    "*.log"
)

# Create tar archive (requires tar to be available on Windows)
$archiveName = "$appName.tar.gz"

Write-Host "Compressing files..." -ForegroundColor Yellow
tar --exclude='node_modules' --exclude='vendor' --exclude='.git' --exclude='storage/logs/*' --exclude='storage/framework/cache/*' --exclude='storage/framework/sessions/*' --exclude='storage/framework/views/*' --exclude='public/build' --exclude='.env' -czf $archiveName .

if (Test-Path $archiveName) {
    Write-Host "✓ Archive created: $archiveName" -ForegroundColor Green
    
    Write-Host ""
    Write-Host "Transferring to server..." -ForegroundColor Yellow
    
    # Transfer deployment script first
    scp deploy-to-debian.sh "${serverUser}@${serverIP}:~/"
    
    # Transfer application archive
    scp $archiveName "${serverUser}@${serverIP}:~/"
    
    Write-Host "✓ Files transferred successfully" -ForegroundColor Green
    Write-Host ""
    Write-Host "Now run these commands on your Debian server:" -ForegroundColor Cyan
    Write-Host "  chmod +x ~/deploy-to-debian.sh" -ForegroundColor White
    Write-Host "  ~/deploy-to-debian.sh" -ForegroundColor White
    Write-Host ""
    
    # Clean up local archive
    Remove-Item $archiveName
    Write-Host "✓ Local archive cleaned up" -ForegroundColor Green
} else {
    Write-Host "Error: Failed to create archive" -ForegroundColor Red
    exit 1
}
