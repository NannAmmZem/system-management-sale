#!/bin/bash

# Diagnose and fix HTTP 500 errors
# Run this on your Debian server

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

APP_DIR="/var/www/sms-app"

echo -e "${BLUE}=========================================="
echo "Diagnosing HTTP 500 Error"
echo "==========================================${NC}"
echo ""

cd $APP_DIR

echo -e "${YELLOW}1. Checking Laravel logs...${NC}"
if [ -f "storage/logs/laravel.log" ]; then
    echo "Last 30 lines of Laravel log:"
    echo "-----------------------------------"
    tail -n 30 storage/logs/laravel.log
    echo "-----------------------------------"
else
    echo -e "${RED}No Laravel log found${NC}"
fi

echo ""
echo -e "${YELLOW}2. Checking Nginx error log...${NC}"
echo "Last 20 lines of Nginx error log:"
echo "-----------------------------------"
sudo tail -n 20 /var/log/nginx/error.log
echo "-----------------------------------"

echo ""
echo -e "${YELLOW}3. Checking PHP-FPM log...${NC}"
if [ -f "/var/log/php8.4-fpm.log" ]; then
    echo "Last 20 lines of PHP-FPM log:"
    echo "-----------------------------------"
    sudo tail -n 20 /var/log/php8.4-fpm.log
    echo "-----------------------------------"
fi

echo ""
echo -e "${YELLOW}4. Checking file permissions...${NC}"
ls -la storage/
ls -la bootstrap/cache/
if [ -f "database/database.sqlite" ]; then
    ls -la database/database.sqlite
fi

echo ""
echo -e "${YELLOW}5. Fixing common issues...${NC}"

# Create missing directories
echo "Creating missing directories..."
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Fix permissions
echo "Fixing permissions..."
sudo chown -R www-data:www-data storage
sudo chown -R www-data:www-data bootstrap/cache
sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/cache

if [ -f "database/database.sqlite" ]; then
    sudo chown www-data:www-data database/database.sqlite
    sudo chown www-data:www-data database/
    sudo chmod 664 database/database.sqlite
    sudo chmod 775 database/
fi

echo -e "${GREEN}✓ Permissions fixed${NC}"

# Clear all caches
echo ""
echo "Clearing all caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo -e "${GREEN}✓ Caches cleared${NC}"

# Check .env file
echo ""
echo -e "${YELLOW}6. Checking .env configuration...${NC}"
if [ ! -f ".env" ]; then
    echo -e "${RED}✗ .env file missing!${NC}"
    cp .env.example .env
    php artisan key:generate --force
    echo -e "${GREEN}✓ .env created${NC}"
else
    echo -e "${GREEN}✓ .env exists${NC}"
fi

# Check APP_KEY
if ! grep -q "APP_KEY=base64:" .env; then
    echo -e "${YELLOW}Generating application key...${NC}"
    php artisan key:generate --force
    echo -e "${GREEN}✓ APP_KEY generated${NC}"
fi

# Check database configuration
echo ""
echo -e "${YELLOW}7. Checking database...${NC}"
if [ -f "database/database.sqlite" ]; then
    echo -e "${GREEN}✓ Database file exists${NC}"
    
    # Test database connection
    if php artisan migrate:status 2>&1 | grep -q "Migration name"; then
        echo -e "${GREEN}✓ Database connection working${NC}"
    else
        echo -e "${RED}✗ Database connection failed${NC}"
        echo "Trying to fix database configuration..."
        
        # Ensure .env has correct database settings
        sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
        sed -i "s|DB_DATABASE=.*|DB_DATABASE=$APP_DIR/database/database.sqlite|" .env
        
        # Comment out MySQL settings
        sed -i 's/^DB_HOST=/#DB_HOST=/' .env
        sed -i 's/^DB_PORT=/#DB_PORT=/' .env
        sed -i 's/^DB_USERNAME=/#DB_USERNAME=/' .env
        sed -i 's/^DB_PASSWORD=/#DB_PASSWORD=/' .env
        
        php artisan config:clear
        echo -e "${GREEN}✓ Database configuration updated${NC}"
    fi
else
    echo -e "${RED}✗ Database file missing${NC}"
    touch database/database.sqlite
    sudo chown www-data:www-data database/database.sqlite
    sudo chmod 664 database/database.sqlite
    php artisan migrate --force
    echo -e "${GREEN}✓ Database created and migrated${NC}"
fi

# Check storage link
echo ""
echo -e "${YELLOW}8. Checking storage link...${NC}"
if [ ! -L "public/storage" ]; then
    php artisan storage:link
    echo -e "${GREEN}✓ Storage linked${NC}"
else
    echo -e "${GREEN}✓ Storage link exists${NC}"
fi

# Optimize
echo ""
echo -e "${YELLOW}9. Optimizing application...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo -e "${GREEN}✓ Application optimized${NC}"

# Restart services
echo ""
echo -e "${YELLOW}10. Restarting services...${NC}"
sudo systemctl restart php8.4-fpm
sudo systemctl restart nginx
echo -e "${GREEN}✓ Services restarted${NC}"

# Test PHP execution
echo ""
echo -e "${YELLOW}11. Testing PHP execution...${NC}"
cd public
php -r "echo 'PHP is working';" && echo -e " ${GREEN}✓${NC}" || echo -e " ${RED}✗${NC}"
cd ..

echo ""
echo -e "${GREEN}=========================================="
echo "Diagnosis Complete"
echo "==========================================${NC}"
echo ""
echo "Try accessing the site again: http://192.168.233.100"
echo ""
echo "If still not working, check the logs above for specific errors."
echo ""
echo "Common issues to look for:"
echo "  - Permission denied errors"
echo "  - Database connection errors"
echo "  - Missing APP_KEY"
echo "  - PHP extension errors"
echo ""
echo "To view live logs:"
echo "  tail -f storage/logs/laravel.log"
echo ""
