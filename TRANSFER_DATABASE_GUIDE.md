# Transfer SMS App with Database to Debian

## Quick Steps

### On Windows PowerShell:

```powershell
# Navigate to your project
cd "C:\laragon\www\Sale Management System\sms-app"

# Transfer everything including database
.\transfer-with-database.ps1
```

### On Debian Server:

```bash
# Run the fix/setup script
~/fix-deployment.sh
```

---

## What Gets Transferred

✓ All application files  
✓ Your existing SQLite database with all data  
✓ Configuration files  
✓ Public assets  

**Excluded (will be rebuilt on server):**
- node_modules (rebuilt with npm install)
- vendor (rebuilt with composer install)
- .git directory
- Cache files
- Build files (rebuilt with npm run build)

---

## Database Handling

The script will:

1. **If database exists** (transferred from Windows):
   - Use your existing database with all data
   - Skip migrations (data already exists)
   - Preserve all users, products, sales, etc.

2. **If no database found**:
   - Create a new empty database
   - Run migrations to create tables
   - Optionally seed demo data

---

## After Deployment

Your application will be accessible at: `http://192.168.233.100`

All your existing data will be available:
- Users and roles
- Products and categories
- Sales records
- Customers
- Suppliers
- Attendance records
- Everything from your Windows setup

---

## Database Location on Server

- Path: `/var/www/sms-app/database/database.sqlite`
- Owner: `www-data:www-data`
- Permissions: `664`

---

## Backup Your Database

### On Debian Server:

```bash
# Create backup
cp /var/www/sms-app/database/database.sqlite ~/backup-$(date +%Y%m%d-%H%M%S).sqlite

# Download backup to Windows
# (Run on Windows PowerShell)
scp nann@192.168.233.100:~/backup-*.sqlite .
```

---

## Troubleshooting

### Database Permission Issues

```bash
cd /var/www/sms-app
sudo chown www-data:www-data database/database.sqlite
sudo chown www-data:www-data database/
sudo chmod 664 database/database.sqlite
sudo chmod 775 database/
```

### Check Database

```bash
cd /var/www/sms-app
php artisan tinker
# Then in tinker:
\App\Models\User::count()
\App\Models\Product::count()
\App\Models\Sale::count()
```

### View Database Directly

```bash
sqlite3 /var/www/sms-app/database/database.sqlite
# SQLite commands:
.tables          # List all tables
.schema users    # Show table structure
SELECT * FROM users;  # Query data
.quit            # Exit
```

---

## Sync Database Changes

If you make changes on Windows and want to update the server:

```powershell
# On Windows - transfer just the database
scp database\database.sqlite nann@192.168.233.100:~/new-database.sqlite
```

```bash
# On Debian - backup and replace
cd /var/www/sms-app
cp database/database.sqlite database/database.sqlite.backup
mv ~/new-database.sqlite database/database.sqlite
sudo chown www-data:www-data database/database.sqlite
sudo chmod 664 database/database.sqlite
```

---

## Alternative: Use MySQL Instead

If you prefer MySQL over SQLite:

### Install MySQL on Debian:

```bash
sudo apt install -y mysql-server
sudo mysql_secure_installation
```

### Create Database:

```bash
sudo mysql
```

```sql
CREATE DATABASE sms_app;
CREATE USER 'smsuser'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON sms_app.* TO 'smsuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Update .env:

```bash
cd /var/www/sms-app
nano .env
```

Change:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sms_app
DB_USERNAME=smsuser
DB_PASSWORD=your_password
```

### Migrate Data:

```bash
php artisan migrate --force
# Import your data or seed
```

---

## Need Help?

Check logs:
```bash
# Application logs
tail -f /var/www/sms-app/storage/logs/laravel.log

# Nginx logs
sudo tail -f /var/log/nginx/error.log

# PHP-FPM logs
sudo tail -f /var/log/php8.4-fpm.log
```
