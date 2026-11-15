# ============================================
# Auto Commit & Push Script
# ============================================
# Automatically stages, commits, and pushes changes to GitHub

Write-Host "=====================================" -ForegroundColor Cyan
Write-Host "  Auto Commit & Push Script" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""

$projectPath = "c:\laragon\www\Sale Management System\sms-app"
Set-Location $projectPath

# Function to show progress
function Show-Step {
    param($message, $color = "Yellow")
    Write-Host "`n[$([DateTime]::Now.ToString('HH:mm:ss'))] $message" -ForegroundColor $color
}

# Check if there are changes
Show-Step "Checking for changes..." "Cyan"
$status = git status --porcelain
if (-not $status) {
    Show-Step "No changes to commit!" "Green"
    exit 0
}

# Show changes
Write-Host "`nChanges detected:" -ForegroundColor Yellow
git status --short

# Get commit message
Write-Host ""
$commitMessage = Read-Host "Enter commit message (or press Enter for auto-generated)"
if ([string]::IsNullOrWhiteSpace($commitMessage)) {
    $commitMessage = "Auto-update: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
}

# Stage all changes
Show-Step "Staging all changes..." "Cyan"
git add .

# Commit changes
Show-Step "Committing changes..." "Cyan"
git commit -m "$commitMessage"
if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] Failed to commit changes" -ForegroundColor Red
    exit 1
}

# Push to GitHub
Show-Step "Pushing to GitHub..." "Cyan"
git push origin main
if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] Failed to push to GitHub" -ForegroundColor Red
    Write-Host "You may need to pull first or check your credentials" -ForegroundColor Yellow
    exit 1
}

# Success
Write-Host ""
Write-Host "=====================================" -ForegroundColor Green
Write-Host "  Successfully Pushed to GitHub!" -ForegroundColor Green
Write-Host "=====================================" -ForegroundColor Green
Write-Host ""
Show-Step "Changes are now live on GitHub!" "Green"
Write-Host ""
