# Docker Deployment Guide

## Step 1: Transfer Files to Debian Server

On Windows PowerShell:

```powershell
cd "C:\laragon\www\Sale Management System\sms-app"

# Create archive including Docker files
tar --exclude='node_modules' --exclude='vendor' --exclude='.git' -czf sms-app-docker.tar.gz .

# Transfer to server
scp sms-app-docker.tar.gz nann@192.168.233.100:~/
scp docker-deploy.sh nann@192.168.233.100:~/
```

## Step 2: Deploy on Debian Server

On Debian server:

```bash
# Extract files
cd /var/www/sms-app
tar -xzf ~/sms-app-docker.tar.gz
rm ~/sms-app-docker.tar.gz

# Make deploy script executable
chmod +x ~/docker-deploy.sh

# Run deployment
~/docker-deploy.sh
```

The script will:
- Install Docker if not present
- Stop existing Nginx/PHP-FPM services
- Build Docker image
- Start containers
- Set up Laravel
- Configure everything automatically

## Step 3: Access Your Application

Open browser: `http://192.168.233.100`

---

## Docker Management Commands

```bash
cd /var/www/sms-app

# View logs
docker compose logs -f

# View only app logs
docker compose logs -f app

# Stop containers
docker compose stop

# Start containers
docker compose start

# Restart containers
docker compose restart

# Stop and remove containers
docker compose down

# Rebuild and restart
docker compose up -d --build

# Execute commands inside container
docker compose exec app php artisan migrate
docker compose exec app php artisan cache:clear

# Access container shell
docker compose exec app bash
```

---

## Benefits of Docker Deployment

✅ Isolated environment  
✅ Easy to replicate  
✅ No conflicts with system packages  
✅ Easy backup and restore  
✅ Portable across servers  
✅ Easy updates and rollbacks  

---

## Updating Your Application

```bash
cd /var/www/sms-app

# Pull latest code or transfer new files
# Then:

docker compose down
docker compose up -d --build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
```

---

## Backup Database

```bash
# Copy database from container
docker compose exec app cp /var/www/database/database.sqlite /var/www/database/backup.sqlite

# Or backup from host
cp /var/www/sms-app/database/database.sqlite ~/backup-$(date +%Y%m%d).sqlite
```

---

## Troubleshooting

### Container won't start
```bash
docker compose logs app
```

### Port 80 already in use
```bash
sudo systemctl stop nginx
sudo systemctl stop apache2
docker compose restart
```

### Permission issues
```bash
docker compose exec app chown -R www-data:www-data /var/www/storage
docker compose exec app chmod -R 775 /var/www/storage
```

### Rebuild from scratch
```bash
docker compose down
docker compose up -d --build --force-recreate
```
