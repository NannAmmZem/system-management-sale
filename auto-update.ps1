# ============================================
# Auto Update Script for SMS Application
# ============================================
# This script automatically pulls changes from Git and updates the application

Write-Host "=====================================" -ForegroundColor Cyan
Write-Host "  SMS Auto Update Script" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""

$projectPath = "c:\laragon\www\Sale Management System\sms-app"
Set-Location $projectPath

# Function to show progress
function Show-Step {
    param($message, $color = "Yellow")
    Write-Host "`n[$([DateTime]::Now.ToString('HH:mm:ss'))] $message" -ForegroundColor $color
}

# Function to handle errors
function Handle-Error {
    param($message)
    Write-Host "`n[ERROR] $message" -ForegroundColor Red
    Write-Host "Update process stopped." -ForegroundColor Red
    exit 1
}

# 1. Check Git status
Show-Step "Checking Git status..." "Cyan"
$gitStatus = git status --porcelain
if ($gitStatus) {
    Write-Host "Working directory has changes:" -ForegroundColor Yellow
    git status --short
    Write-Host ""
    $response = Read-Host "Do you want to stash these changes? (Y/N)"
    if ($response -eq "Y" -or $response -eq "y") {
        Show-Step "Stashing local changes..."
        git stash save "Auto-stash before update at $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
        if ($LASTEXITCODE -ne 0) { Handle-Error "Failed to stash changes" }
    } else {
        Write-Host "Skipping update to preserve local changes." -ForegroundColor Yellow
        exit 0
    }
}

# 2. Fetch latest changes
Show-Step "Fetching latest changes from GitHub..." "Cyan"
git fetch origin
if ($LASTEXITCODE -ne 0) { Handle-Error "Failed to fetch from remote" }

# 3. Check if updates are available
$LOCAL = git rev-parse @
$REMOTE = git rev-parse '@{u}'

if ($LOCAL -eq $REMOTE) {
    Show-Step "Already up to date! No updates needed." "Green"
    exit 0
}

Show-Step "Updates available! Pulling changes..." "Yellow"

# 4. Pull latest changes
git pull origin main
if ($LASTEXITCODE -ne 0) { Handle-Error "Failed to pull changes" }

Show-Step "Code updated successfully!" "Green"

# 5. Install/Update Composer dependencies
Show-Step "Updating Composer dependencies..." "Cyan"
composer install --no-interaction --optimize-autoloader
if ($LASTEXITCODE -ne 0) { Handle-Error "Composer install failed" }

# 6. Install/Update NPM dependencies
Show-Step "Updating NPM dependencies..." "Cyan"
npm install
if ($LASTEXITCODE -ne 0) { Handle-Error "NPM install failed" }

# 7. Build frontend assets
Show-Step "Building frontend assets..." "Cyan"
npm run build
if ($LASTEXITCODE -ne 0) { Handle-Error "NPM build failed" }

# 8. Run migrations
Show-Step "Running database migrations..." "Cyan"
$migrateResponse = Read-Host "Run database migrations? (Y/N)"
if ($migrateResponse -eq "Y" -or $migrateResponse -eq "y") {
    php artisan migrate --force
    if ($LASTEXITCODE -ne 0) { 
        Write-Host "Migration failed, but continuing..." -ForegroundColor Yellow
    }
}

# 9. Clear caches
Show-Step "Clearing application caches..." "Cyan"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 10. Optimize application
Show-Step "Optimizing application..." "Cyan"
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 11. Storage link (if needed)
if (-not (Test-Path "$projectPath\public\storage")) {
    Show-Step "Creating storage symlink..." "Cyan"
    php artisan storage:link
}

# Summary
Write-Host ""
Write-Host "=====================================" -ForegroundColor Green
Write-Host "  Update Completed Successfully!" -ForegroundColor Green
Write-Host "=====================================" -ForegroundColor Green
Write-Host ""
Show-Step "Application is now running the latest version!" "Green"
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host "1. Restart your development server if needed" -ForegroundColor White
Write-Host "2. Test the application: http://127.0.0.1:8000" -ForegroundColor White
Write-Host "3. Check for any errors in the logs" -ForegroundColor White
Write-Host ""

# Optional: Restart services
$restartResponse = Read-Host "Do you want to restart the development server? (Y/N)"
if ($restartResponse -eq "Y" -or $restartResponse -eq "y") {
    Show-Step "Note: Restart manually using 'composer dev'" "Yellow"
}
