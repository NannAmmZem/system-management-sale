# 🔄 Git Auto-Update System

Complete guide for automatically updating your SMS application from GitHub.

---

## 📋 Quick Reference

### One-Click Scripts

| Script | Purpose | When to Use |
|--------|---------|-------------|
| **quick-update.bat** | Pull latest changes and update | When you want to get latest code from GitHub |
| **quick-push.bat** | Commit and push your changes | When you made changes and want to upload to GitHub |
| **auto-update.ps1** | Advanced update with checks | For detailed update with safety checks |
| **auto-commit-push.ps1** | Advanced commit and push | For detailed commit process |

---

## 🚀 Quick Start

### Method 1: One-Click Update (Easiest)
1. Double-click **`quick-update.bat`**
2. Wait for the process to complete
3. Done! Your app is updated

### Method 2: One-Click Push (Upload Changes)
1. Double-click **`quick-push.bat`**
2. Enter your commit message
3. Wait for upload to complete
4. Done! Changes are on GitHub

---

## 📖 Detailed Usage

### 🔽 Pulling Updates from GitHub

#### Option A: Quick Update (Recommended)
```bash
# Double-click this file
quick-update.bat
```

What it does:
1. ✅ Pulls latest code from GitHub
2. ✅ Installs new dependencies
3. ✅ Builds frontend
4. ✅ Clears caches
5. ✅ Optimizes application

#### Option B: Advanced Update
```powershell
# Right-click → Run with PowerShell
auto-update.ps1
```

Additional features:
- Checks for updates before pulling
- Stashes local changes if needed
- Runs database migrations (optional)
- More detailed progress reporting

#### Option C: Manual Update
```bash
git pull origin main
composer install
npm install && npm run build
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

---

### 🔼 Pushing Changes to GitHub

#### Option A: Quick Push (Recommended)
```bash
# Double-click this file
quick-push.bat
```

Steps:
1. Shows your changes
2. Asks for commit message
3. Stages all files
4. Commits changes
5. Pushes to GitHub

#### Option B: Advanced Push
```powershell
# Right-click → Run with PowerShell
auto-commit-push.ps1
```

Additional features:
- Auto-generates commit message if empty
- Better error handling
- Detailed status reporting

#### Option C: Manual Push
```bash
git add .
git commit -m "Your message here"
git push origin main
```

---

## ⚙️ Automatic Updates (Scheduled Task)

### Set Up Automatic Updates

#### Windows Task Scheduler Setup

1. **Open Task Scheduler**
   - Press `Win + R`
   - Type `taskschd.msc`
   - Press Enter

2. **Create New Task**
   - Click "Create Task" (not "Create Basic Task")
   - Name: `SMS Auto Update`
   - Description: `Automatically updates SMS application from GitHub`
   - ✅ Check "Run whether user is logged on or not"
   - ✅ Check "Run with highest privileges"

3. **Triggers Tab**
   - Click "New"
   - Begin the task: `On a schedule`
   - Settings: Choose your preference:
     - **Daily at 2:00 AM** (recommended)
     - **Weekly on Sunday**
     - **On startup**

4. **Actions Tab**
   - Click "New"
   - Action: `Start a program`
   - Program/script: `powershell.exe`
   - Add arguments:
     ```
     -ExecutionPolicy Bypass -File "c:\laragon\www\Sale Management System\sms-app\auto-update.ps1"
     ```
   - Start in: `c:\laragon\www\Sale Management System\sms-app`

5. **Conditions Tab**
   - ✅ Start only if computer is on AC power
   - ✅ Wake the computer to run this task

6. **Settings Tab**
   - ✅ Allow task to be run on demand
   - ✅ If task fails, restart every: 1 hour
   - ✅ Stop task if it runs longer than: 1 hour

7. **Save & Test**
   - Click OK
   - Right-click the task → Run
   - Check if it works

---

## 🔧 Manual Git Commands

### Common Operations

#### Check Status
```bash
git status
```

#### Pull Latest Changes
```bash
git pull origin main
```

#### Stage Changes
```bash
# Stage all files
git add .

# Stage specific file
git add filename.php

# Stage specific folder
git add app/
```

#### Commit Changes
```bash
# With message
git commit -m "Your commit message"

# With detailed message
git commit -m "Title" -m "Detailed description"
```

#### Push Changes
```bash
# Push to main branch
git push origin main

# Force push (use with caution!)
git push origin main --force
```

#### View History
```bash
# View commit log
git log --oneline

# View last 5 commits
git log -5

# View with graph
git log --graph --oneline --all
```

#### Undo Changes
```bash
# Discard changes in file
git restore filename.php

# Discard all changes
git restore .

# Undo last commit (keep changes)
git reset --soft HEAD~1

# Undo last commit (discard changes)
git reset --hard HEAD~1
```

#### Stash Changes
```bash
# Save changes temporarily
git stash save "Work in progress"

# List stashes
git stash list

# Apply latest stash
git stash pop

# Apply specific stash
git stash apply stash@{0}
```

---

## 🛡️ Safety Tips

### Before Updating
1. ✅ **Backup your database**
   ```bash
   php artisan backup:run
   ```

2. ✅ **Check for local changes**
   ```bash
   git status
   ```

3. ✅ **Stash or commit changes**
   ```bash
   git stash save "Before update"
   ```

### After Updating
1. ✅ **Test the application**
   - Visit http://127.0.0.1:8000
   - Test key features

2. ✅ **Check logs for errors**
   - Check `storage/logs/laravel.log`

3. ✅ **Verify database**
   - Run `php artisan migrate:status`

---

## 🐛 Troubleshooting

### Problem: Merge Conflicts
```bash
# See conflicted files
git status

# Abort merge and start over
git merge --abort

# Or resolve conflicts manually, then:
git add .
git commit -m "Resolved conflicts"
```

### Problem: "Your local changes would be overwritten"
```bash
# Option 1: Stash changes
git stash save "Local changes"
git pull origin main
git stash pop

# Option 2: Discard changes (BE CAREFUL!)
git reset --hard HEAD
git pull origin main
```

### Problem: Authentication Failed
1. Update your Git credentials
2. Use GitHub Personal Access Token
3. Configure:
   ```bash
   git config --global credential.helper manager-core
   ```

### Problem: Changes Not Showing After Update
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild frontend
npm run build

# Hard refresh browser (Ctrl + Shift + R)
```

### Problem: Database Migration Issues
```bash
# Check migration status
php artisan migrate:status

# Rollback last migration
php artisan migrate:rollback

# Fresh migration (CAUTION: Deletes data!)
php artisan migrate:fresh --seed
```

---

## 📊 Update Log

Keep track of your updates in a log file:

```bash
# View update history
git log --oneline -10

# See what changed
git diff HEAD~1 HEAD

# See file changes
git show --stat
```

---

## 🔐 Security Reminders

### Never Commit These Files:
- ✅ `.env` (already in .gitignore)
- ✅ `node_modules/` (already in .gitignore)
- ✅ `vendor/` (already in .gitignore)
- ✅ `storage/logs/` (already in .gitignore)
- ✅ Database backups

### Check Before Pushing:
```bash
# View what will be pushed
git diff --stat origin/main

# Review changes
git status
```

---

## 📞 Support

If you encounter issues:
1. Check the troubleshooting section above
2. Review `storage/logs/laravel.log`
3. Check Git output for error messages
4. Ensure you have internet connection
5. Verify GitHub credentials are correct

---

## 🎯 Best Practices

1. **Always backup before major updates**
2. **Test updates on development before production**
3. **Commit changes before pulling**
4. **Use meaningful commit messages**
5. **Review changes before pushing**
6. **Keep dependencies updated**
7. **Monitor logs after updates**

---

## 📚 Additional Resources

- [Git Documentation](https://git-scm.com/doc)
- [GitHub Guides](https://guides.github.com/)
- [Laravel Deployment](https://laravel.com/docs/deployment)

---

**Last Updated:** 2026-02-07
