#!/bin/bash

# Fix deployment issues script
# Run this on your Debian server

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

APP_DIR="/var/www/sms-app"

echo -e "${BLUE}=========================================="
echo "SMS App Troubleshooting & Fix Script"
echo "==========================================${NC}"
echo ""

# Check if app directory exists
echo -e "${YELLOW}Checking application directory...${NC}"
if [ ! -d "$APP_DIR" ]; then
    echo -e "${RED}✗ Application directory not found: $APP_DIR${NC}"
    echo "Creating directory..."
    sudo mkdir -p $APP_DIR
    sudo chown -R $USER:www-data $APP_DIR
    echo -e "${GREEN}✓ Directory created${NC}"
else
    echo -e "${GREEN}✓ Application directory exists${NC}"
fi

# Check if files exist
echo ""
echo -e "${YELLOW}Checking application files...${NC}"
if [ ! -f "$APP_DIR/public/index.php" ]; then
    echo -e "${RED}✗ Application files not found${NC}"
    echo ""
    echo "Please transfer your files first:"
    echo "1. On Windows, create archive:"
    echo "   tar --exclude='node_modules' --exclude='vendor' --exclude='.git' -czf sms-app.tar.gz ."
    echo ""
    echo "2. Transfer to server:"
    echo "   scp sms-app.tar.gz nann@192.168.233.100:~/sms-app.tar.gz"
    echo ""
    read -p "Press Enter after transferring files, or Ctrl+C to exit..."
    
    if [ -f ~/sms-app.tar.gz ]; then
        echo "Extracting files..."
        cd $APP_DIR
        tar -xzf ~/sms-app.tar.gz
        rm ~/sms-app.tar.gz
        echo -e "${GREEN}✓ Files extracted${NC}"
    else
        echo -e "${RED}✗ Archive not found at ~/sms-app.tar.gz${NC}"
        exit 1
    fi
else
    echo -e "${GREEN}✓ Application files exist${NC}"
fi

cd $APP_DIR

# Check PHP
echo ""
echo -e "${YELLOW}Checking PHP installation...${NC}"
if ! command -v php &> /dev/null; then
    echo -e "${RED}✗ PHP not installed${NC}"
    echo "Installing PHP 8.4..."
    sudo apt update
    sudo apt install -y software-properties-common
    echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/php.list
    wget -qO - https://packages.sury.org/php/apt.gpg | sudo apt-key add -
    sudo apt update
    sudo apt install -y php8.4 php8.4-cli php8.4-fpm php8.4-common php8.4-mysql \
        php8.4-zip php8.4-gd php8.4-mbstring php8.4-curl php8.4-xml php8.4-bcmath \
        php8.4-sqlite3 php8.4-intl php8.4-dom
fi
echo -e "${GREEN}✓ PHP installed: $(php -v | head -n 1)${NC}"

# Check Composer
echo ""
echo -e "${YELLOW}Checking Composer...${NC}"
if ! command -v composer &> /dev/null; then
    echo "Installing Composer..."
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    sudo chmod +x /usr/local/bin/composer
fi
echo -e "${GREEN}✓ Composer installed${NC}"

# Check Node.js
echo ""
echo -e "${YELLOW}Checking Node.js...${NC}"
if ! command -v node &> /dev/null; then
    echo "Installing Node.js..."
    curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
    sudo apt install -y nodejs
fi
echo -e "${GREEN}✓ Node.js installed: $(node -v)${NC}"

# Install dependencies
echo ""
echo -e "${YELLOW}Installing dependencies...${NC}"
if [ ! -d "vendor" ]; then
    echo "Installing Composer dependencies..."
    composer install --optimize-autoloader --no-dev --no-interaction
    echo -e "${GREEN}✓ Composer dependencies installed${NC}"
else
    echo -e "${GREEN}✓ Vendor directory exists${NC}"
fi

if [ ! -d "node_modules" ]; then
    echo "Installing npm dependencies..."
    npm install
    echo -e "${GREEN}✓ npm dependencies installed${NC}"
else
    echo -e "${GREEN}✓ node_modules directory exists${NC}"
fi

# Build assets
echo ""
echo -e "${YELLOW}Building frontend assets...${NC}"
if [ ! -d "public/build" ]; then
    npm run build
    echo -e "${GREEN}✓ Assets built${NC}"
else
    echo -e "${GREEN}✓ Build directory exists${NC}"
fi

# Setup .env
echo ""
echo -e "${YELLOW}Checking environment configuration...${NC}"
if [ ! -f ".env" ]; then
    echo "Creating .env file..."
    cp .env.example .env
    
    # Configure for production
    sed -i 's/APP_ENV=.*/APP_ENV=production/' .env
    sed -i 's/APP_DEBUG=.*/APP_DEBUG=false/' .env
    sed -i 's|APP_URL=.*|APP_URL=http://192.168.233.100|' .env
    
    # Setup SQLite by default
    sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=$APP_DIR/database/database.sqlite|" .env
    
    php artisan key:generate --force
    echo -e "${GREEN}✓ Environment configured${NC}"
else
    echo -e "${GREEN}✓ .env file exists${NC}"
fi

# Setup database
echo ""
echo -e "${YELLOW}Setting up database...${NC}"
if [ -f "database/database.sqlite" ]; then
    echo -e "${GREEN}✓ Existing database found (transferred from Windows)${NC}"
    echo "Database will be used as-is with existing data."
else
    echo "Creating new SQLite database..."
    touch database/database.sqlite
    chmod 664 database/database.sqlite
    echo -e "${GREEN}✓ New SQLite database created${NC}"
    
    # Run migrations only for new database
    echo ""
    echo -e "${YELLOW}Running migrations...${NC}"
    php artisan migrate --force
    echo -e "${GREEN}✓ Migrations completed${NC}"
    
    # Ask about seeding
    read -p "Do you want to seed demo data? (y/n): " seed_choice
    if [ "$seed_choice" = "y" ]; then
        php artisan db:seed --force
        echo -e "${GREEN}✓ Database seeded${NC}"
    fi
fi

# Set permissions
echo ""
echo -e "${YELLOW}Setting permissions...${NC}"
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

if [ -f "database/database.sqlite" ]; then
    sudo chown www-data:www-data database/database.sqlite
    sudo chown www-data:www-data database/
    sudo chmod 664 database/database.sqlite
    sudo chmod 775 database/
    echo -e "${GREEN}✓ Database permissions set${NC}"
fi

# Ensure public directory is readable
sudo chown -R www-data:www-data public
sudo chmod -R 755 public

echo -e "${GREEN}✓ Permissions set${NC}"

# Clear and cache
echo ""
echo -e "${YELLOW}Optimizing application...${NC}"
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo -e "${GREEN}✓ Application optimized${NC}"

# Check Nginx
echo ""
echo -e "${YELLOW}Checking Nginx configuration...${NC}"
if ! command -v nginx &> /dev/null; then
    echo "Installing Nginx..."
    sudo apt install -y nginx
fi

# Create Nginx config
echo "Configuring Nginx..."
sudo tee /etc/nginx/sites-available/sms-app > /dev/null <<'EOF'
server {
    listen 80;
    server_name 192.168.233.100;
    root /var/www/sms-app/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php index.html;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Enable site
sudo ln -sf /etc/nginx/sites-available/sms-app /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

# Test Nginx config
if sudo nginx -t; then
    echo -e "${GREEN}✓ Nginx configuration valid${NC}"
else
    echo -e "${RED}✗ Nginx configuration error${NC}"
    exit 1
fi

# Restart services
echo ""
echo -e "${YELLOW}Restarting services...${NC}"
sudo systemctl restart php8.4-fpm
sudo systemctl restart nginx
sudo systemctl enable nginx
sudo systemctl enable php8.4-fpm
echo -e "${GREEN}✓ Services restarted${NC}"

# Check services status
echo ""
echo -e "${YELLOW}Checking services status...${NC}"
if systemctl is-active --quiet nginx; then
    echo -e "${GREEN}✓ Nginx is running${NC}"
else
    echo -e "${RED}✗ Nginx is not running${NC}"
    sudo systemctl status nginx
fi

if systemctl is-active --quiet php8.4-fpm; then
    echo -e "${GREEN}✓ PHP-FPM is running${NC}"
else
    echo -e "${RED}✗ PHP-FPM is not running${NC}"
    sudo systemctl status php8.4-fpm
fi

# Test application
echo ""
echo -e "${YELLOW}Testing application...${NC}"
if [ -f "public/index.php" ]; then
    echo -e "${GREEN}✓ index.php exists${NC}"
    
    # Test PHP execution
    cd public
    if php -r "require 'index.php';" 2>/dev/null; then
        echo -e "${GREEN}✓ PHP can execute index.php${NC}"
    fi
    cd ..
fi

# Create admin user
echo ""
echo -e "${YELLOW}Creating admin user...${NC}"
php artisan create:admin || echo -e "${YELLOW}Note: Admin user may already exist${NC}"

echo ""
echo -e "${GREEN}=========================================="
echo "✓ Setup Complete!"
echo "==========================================${NC}"
echo ""
echo "Your application should now be accessible at:"
echo -e "${GREEN}http://192.168.233.100${NC}"
echo ""
echo "If you still see errors, check logs:"
echo "  sudo tail -f /var/log/nginx/error.log"
echo "  tail -f $APP_DIR/storage/logs/laravel.log"
echo ""
