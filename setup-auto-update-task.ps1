# ============================================
# Setup Auto-Update Scheduled Task
# ============================================
# Run this script as Administrator to set up automatic updates

# Check if running as Administrator
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

if (-not $isAdmin) {
    Write-Host "ERROR: This script must be run as Administrator!" -ForegroundColor Red
    Write-Host "Right-click this file and select 'Run as Administrator'" -ForegroundColor Yellow
    pause
    exit 1
}

Write-Host "=====================================" -ForegroundColor Cyan
Write-Host "  Setup Auto-Update Task" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""

$projectPath = "c:\laragon\www\Sale Management System\sms-app"
$scriptPath = "$projectPath\auto-update.ps1"
$taskName = "SMS-AutoUpdate"

# Check if task already exists
$existingTask = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue

if ($existingTask) {
    Write-Host "Task '$taskName' already exists!" -ForegroundColor Yellow
    $response = Read-Host "Do you want to remove and recreate it? (Y/N)"
    if ($response -ne "Y" -and $response -ne "y") {
        Write-Host "Setup cancelled." -ForegroundColor Yellow
        exit 0
    }
    Unregister-ScheduledTask -TaskName $taskName -Confirm:$false
    Write-Host "Existing task removed." -ForegroundColor Green
}

Write-Host ""
Write-Host "Schedule Options:" -ForegroundColor Cyan
Write-Host "1. Daily at 2:00 AM (Recommended)" -ForegroundColor White
Write-Host "2. Weekly on Sunday at 2:00 AM" -ForegroundColor White
Write-Host "3. On System Startup" -ForegroundColor White
Write-Host "4. Custom (Manual configuration)" -ForegroundColor White
Write-Host ""

$choice = Read-Host "Select option (1-4)"

$action = New-ScheduledTaskAction -Execute "powershell.exe" `
    -Argument "-ExecutionPolicy Bypass -WindowStyle Hidden -File `"$scriptPath`"" `
    -WorkingDirectory $projectPath

switch ($choice) {
    "1" {
        $trigger = New-ScheduledTaskTrigger -Daily -At "2:00AM"
        $description = "Automatically updates SMS application from GitHub daily at 2:00 AM"
    }
    "2" {
        $trigger = New-ScheduledTaskTrigger -Weekly -DaysOfWeek Sunday -At "2:00AM"
        $description = "Automatically updates SMS application from GitHub weekly on Sunday at 2:00 AM"
    }
    "3" {
        $trigger = New-ScheduledTaskTrigger -AtStartup
        $description = "Automatically updates SMS application from GitHub on system startup"
    }
    "4" {
        Write-Host ""
        Write-Host "Please configure the task manually in Task Scheduler." -ForegroundColor Yellow
        Write-Host "Task Name: $taskName" -ForegroundColor White
        Write-Host "Script Path: $scriptPath" -ForegroundColor White
        exit 0
    }
    default {
        Write-Host "Invalid choice!" -ForegroundColor Red
        exit 1
    }
}

# Create task settings
$settings = New-ScheduledTaskSettingsSet `
    -AllowStartIfOnBatteries `
    -DontStopIfGoingOnBatteries `
    -StartWhenAvailable `
    -RunOnlyIfNetworkAvailable `
    -ExecutionTimeLimit (New-TimeSpan -Hours 1)

# Get current user
$currentUser = [System.Security.Principal.WindowsIdentity]::GetCurrent().Name

# Register the task
Write-Host ""
Write-Host "Creating scheduled task..." -ForegroundColor Cyan

try {
    Register-ScheduledTask `
        -TaskName $taskName `
        -Action $action `
        -Trigger $trigger `
        -Settings $settings `
        -Description $description `
        -User $currentUser `
        -RunLevel Highest `
        -Force | Out-Null
    
    Write-Host "✓ Task created successfully!" -ForegroundColor Green
    Write-Host ""
    Write-Host "=====================================" -ForegroundColor Green
    Write-Host "  Setup Complete!" -ForegroundColor Green
    Write-Host "=====================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "Task Details:" -ForegroundColor Cyan
    Write-Host "  Name: $taskName" -ForegroundColor White
    Write-Host "  Script: $scriptPath" -ForegroundColor White
    Write-Host "  Trigger: $($trigger.ToString())" -ForegroundColor White
    Write-Host ""
    Write-Host "You can manage this task in Task Scheduler:" -ForegroundColor Yellow
    Write-Host "  1. Press Win + R" -ForegroundColor White
    Write-Host "  2. Type: taskschd.msc" -ForegroundColor White
    Write-Host "  3. Look for: $taskName" -ForegroundColor White
    Write-Host ""
    
    $testResponse = Read-Host "Do you want to test the task now? (Y/N)"
    if ($testResponse -eq "Y" -or $testResponse -eq "y") {
        Write-Host ""
        Write-Host "Running task..." -ForegroundColor Cyan
        Start-ScheduledTask -TaskName $taskName
        Write-Host "✓ Task started! Check Task Scheduler for results." -ForegroundColor Green
    }
    
} catch {
    Write-Host "ERROR: Failed to create task!" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    exit 1
}

Write-Host ""
pause
