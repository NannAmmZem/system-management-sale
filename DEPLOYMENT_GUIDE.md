# SMS App Deployment Guide - Debian with PHP 8.4

## Quick Start

### Option 1: Automated Deployment (Recommended)

#### On Windows (Local Machine):

1. Open PowerShell in the project directory:
```powershell
cd "C:\laragon\www\Sale Management System\sms-app"
```

2. Run the transfer script:
```powershell
.\transfer-to-server.ps1
```

#### On Debian Server:

1. SSH into your server:
```bash
ssh nann@192.168.233.100
```

2. Make the script executable and run it:
```bash
chmod +x ~/deploy-to-debian.sh
~/deploy-to-debian.sh
```

3. Follow the prompts to complete the setup.

---

### Option 2: Manual Deployment

#### Step 1: Transfer Files

On Windows PowerShell:
```powershell
cd "C:\laragon\www\Sale Management System\sms-app"
scp deploy-to-debian.sh nann@192.168.233.100:~/
tar --exclude='node_modules' --exclude='vendor' --exclude='.git' -czf sms-app.tar.gz .
scp sms-app.tar.gz nann@192.168.233.100:~/
```

#### Step 2: Run Deployment Script

On Debian server:
```bash
chmod +x ~/deploy-to-debian.sh
~/deploy-to-debian.sh
```

---

## What the Script Does

1. ✓ Installs PHP 8.4 and all required extensions
2. ✓ Installs Composer (PHP dependency manager)
3. ✓ Installs Node.js 20.x and npm
4. ✓ Installs and configures Nginx web server
5. ✓ Creates application directory at `/var/www/sms-app`
6. ✓ Extracts your application files
7. ✓ Installs PHP dependencies (composer install)
8. ✓ Builds frontend assets (npm run build)
9. ✓ Sets up environment configuration
10. ✓ Configures database (SQLite or MySQL)
11. ✓ Runs database migrations
12. ✓ Sets proper file permissions
13. ✓ Optimizes for production
14. ✓ Creates admin user

---

## Post-Deployment

### Access Your Application

Open browser: `http://192.168.233.100`

### Useful Commands

```bash
# View application logs
tail -f /var/www/sms-app/storage/logs/laravel.log

# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.4-fpm

# Clear application cache
cd /var/www/sms-app
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Re-optimize after changes
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Create new admin user
php artisan create:admin
```

---

## Troubleshooting

### Check Service Status
```bash
sudo systemctl status nginx
sudo systemctl status php8.4-fpm
```

### Check Nginx Error Logs
```bash
sudo tail -f /var/log/nginx/error.log
```

### Check PHP-FPM Logs
```bash
sudo tail -f /var/log/php8.4-fpm.log
```

### Permission Issues
```bash
cd /var/www/sms-app
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Database Issues (SQLite)
```bash
cd /var/www/sms-app
sudo chown www-data:www-data database/database.sqlite
sudo chmod 664 database/database.sqlite
```

---

## Security Recommendations

1. **Change default passwords** - Update all default credentials
2. **Enable firewall**:
   ```bash
   sudo apt install ufw
   sudo ufw allow 22
   sudo ufw allow 80
   sudo ufw allow 443
   sudo ufw enable
   ```

3. **Set up SSL/HTTPS** (optional but recommended):
   ```bash
   sudo apt install certbot python3-certbot-nginx
   sudo certbot --nginx -d yourdomain.com
   ```

4. **Regular backups**:
   ```bash
   # Backup database
   cp /var/www/sms-app/database/database.sqlite ~/backup-$(date +%Y%m%d).sqlite
   
   # Or for MySQL
   mysqldump -u username -p database_name > backup-$(date +%Y%m%d).sql
   ```

---

## Updating the Application

```bash
cd /var/www/sms-app

# Pull latest changes or transfer new files
# Then run:

composer install --optimize-autoloader --no-dev
npm install
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

sudo systemctl restart php8.4-fpm
```

---

## Support

If you encounter issues:
1. Check the logs (see commands above)
2. Verify all services are running
3. Ensure file permissions are correct
4. Check database connectivity
