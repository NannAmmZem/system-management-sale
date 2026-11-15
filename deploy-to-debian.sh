#!/bin/bash

# SMS App Deployment Script for Debian with PHP 8.4
# Run this script on your Debian server as the nann user

set -e  # Exit on any error

echo "=========================================="
echo "SMS App Deployment Script"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
APP_NAME="sms-app"
APP_DIR="/var/www/$APP_NAME"
DOMAIN_OR_IP="192.168.233.100"

echo -e "${YELLOW}Step 1: Installing PHP 8.4 and extensions...${NC}"
sudo apt update
sudo apt install -y lsb-release ca-certificates apt-transport-https software-properties-common gnupg2 curl wget

# Add Sury PHP repository
if [ ! -f /etc/apt/sources.list.d/php.list ]; then
    echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/php.list
    wget -qO - https://packages.sury.org/php/apt.gpg | sudo apt-key add -
    sudo apt update
fi

sudo apt install -y php8.4 php8.4-cli php8.4-fpm php8.4-common php8.4-mysql \
    php8.4-zip php8.4-gd php8.4-mbstring php8.4-curl php8.4-xml php8.4-bcmath \
    php8.4-sqlite3 php8.4-intl php8.4-redis php8.4-dom

echo -e "${GREEN}✓ PHP 8.4 installed${NC}"
php -v

echo ""
echo -e "${YELLOW}Step 2: Installing Composer...${NC}"
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    sudo chmod +x /usr/local/bin/composer
fi
echo -e "${GREEN}✓ Composer installed${NC}"
composer --version

echo ""
echo -e "${YELLOW}Step 3: Installing Node.js and npm...${NC}"
if ! command -v node &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
    sudo apt install -y nodejs
fi
echo -e "${GREEN}✓ Node.js installed${NC}"
node -v
npm -v

echo ""
echo -e "${YELLOW}Step 4: Installing Nginx...${NC}"
sudo apt install -y nginx
echo -e "${GREEN}✓ Nginx installed${NC}"

echo ""
echo -e "${YELLOW}Step 5: Creating application directory...${NC}"
sudo mkdir -p $APP_DIR
sudo chown -R $USER:www-data $APP_DIR
echo -e "${GREEN}✓ Directory created: $APP_DIR${NC}"

echo ""
echo -e "${YELLOW}Step 6: Configuring Nginx...${NC}"
sudo tee /etc/nginx/sites-available/$APP_NAME > /dev/null <<EOF
server {
    listen 80;
    server_name $DOMAIN_OR_IP;
    root $APP_DIR/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php index.html;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Enable site
sudo ln -sf /etc/nginx/sites-available/$APP_NAME /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

# Test and restart Nginx
sudo nginx -t
sudo systemctl restart nginx
sudo systemctl enable nginx

echo -e "${GREEN}✓ Nginx configured${NC}"

echo ""
echo -e "${YELLOW}Step 7: Ready to deploy application files${NC}"
echo ""
echo "Now you need to transfer your application files to the server."
echo ""
echo "On your LOCAL Windows machine, run:"
echo -e "${GREEN}cd \"C:\\laragon\\www\\Sale Management System\\sms-app\"${NC}"
echo -e "${GREEN}scp -r . nann@$DOMAIN_OR_IP:$APP_DIR/${NC}"
echo ""
echo "Or create a tar archive first (recommended):"
echo -e "${GREEN}tar --exclude='node_modules' --exclude='vendor' --exclude='.git' -czf sms-app.tar.gz .${NC}"
echo -e "${GREEN}scp sms-app.tar.gz nann@$DOMAIN_OR_IP:~/sms-app.tar.gz${NC}"
echo ""
read -p "Press Enter after you've transferred the files..."

echo ""
echo -e "${YELLOW}Step 8: Extracting and setting up application...${NC}"

# Check if tar file exists
if [ -f ~/sms-app.tar.gz ]; then
    cd $APP_DIR
    tar -xzf ~/sms-app.tar.gz
    rm ~/sms-app.tar.gz
    echo -e "${GREEN}✓ Files extracted${NC}"
fi

cd $APP_DIR

# Install PHP dependencies
echo ""
echo -e "${YELLOW}Step 9: Installing Composer dependencies...${NC}"
composer install --optimize-autoloader --no-dev --no-interaction
echo -e "${GREEN}✓ Composer dependencies installed${NC}"

# Install Node dependencies and build
echo ""
echo -e "${YELLOW}Step 10: Building frontend assets...${NC}"
npm install
npm run build
echo -e "${GREEN}✓ Frontend assets built${NC}"

# Setup environment
echo ""
echo -e "${YELLOW}Step 11: Configuring environment...${NC}"
if [ ! -f .env ]; then
    cp .env.example .env
    echo -e "${GREEN}✓ .env file created${NC}"
fi

# Generate app key
php artisan key:generate --force
echo -e "${GREEN}✓ Application key generated${NC}"

# Setup database
echo ""
echo -e "${YELLOW}Step 12: Setting up database...${NC}"
echo "Choose database type:"
echo "1) SQLite (recommended for simple setup)"
echo "2) MySQL"
read -p "Enter choice (1 or 2): " db_choice

if [ "$db_choice" = "1" ]; then
    # SQLite setup
    sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=$APP_DIR/database/database.sqlite|" .env
    touch database/database.sqlite
    chmod 664 database/database.sqlite
    echo -e "${GREEN}✓ SQLite database created${NC}"
else
    # MySQL setup
    read -p "Enter MySQL host (default: 127.0.0.1): " mysql_host
    mysql_host=${mysql_host:-127.0.0.1}
    
    read -p "Enter MySQL database name: " mysql_db
    read -p "Enter MySQL username: " mysql_user
    read -sp "Enter MySQL password: " mysql_pass
    echo ""
    
    sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=mysql/' .env
    sed -i "s/DB_HOST=.*/DB_HOST=$mysql_host/" .env
    sed -i "s/DB_DATABASE=.*/DB_DATABASE=$mysql_db/" .env
    sed -i "s/DB_USERNAME=.*/DB_USERNAME=$mysql_user/" .env
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$mysql_pass/" .env
    echo -e "${GREEN}✓ MySQL configuration updated${NC}"
fi

# Update other env settings
sed -i 's/APP_ENV=.*/APP_ENV=production/' .env
sed -i 's/APP_DEBUG=.*/APP_DEBUG=false/' .env
sed -i "s|APP_URL=.*|APP_URL=http://$DOMAIN_OR_IP|" .env

# Run migrations
echo ""
echo -e "${YELLOW}Step 13: Running database migrations...${NC}"
php artisan migrate --force
echo -e "${GREEN}✓ Migrations completed${NC}"

# Seed database
read -p "Do you want to seed demo data? (y/n): " seed_choice
if [ "$seed_choice" = "y" ]; then
    php artisan db:seed --force
    echo -e "${GREEN}✓ Database seeded${NC}"
fi

# Set permissions
echo ""
echo -e "${YELLOW}Step 14: Setting permissions...${NC}"
sudo chown -R www-data:www-data $APP_DIR/storage $APP_DIR/bootstrap/cache
sudo chmod -R 775 $APP_DIR/storage $APP_DIR/bootstrap/cache

# If using SQLite, ensure www-data can write to it
if [ -f database/database.sqlite ]; then
    sudo chown www-data:www-data database/database.sqlite
    sudo chmod 664 database/database.sqlite
fi

echo -e "${GREEN}✓ Permissions set${NC}"

# Optimize for production
echo ""
echo -e "${YELLOW}Step 15: Optimizing for production...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
echo -e "${GREEN}✓ Application optimized${NC}"

# Create admin user
echo ""
echo -e "${YELLOW}Step 16: Creating admin user...${NC}"
php artisan create:admin

echo ""
echo -e "${GREEN}=========================================="
echo "✓ Deployment Complete!"
echo "==========================================${NC}"
echo ""
echo "Your application is now accessible at:"
echo -e "${GREEN}http://$DOMAIN_OR_IP${NC}"
echo ""
echo "Useful commands:"
echo "  - View logs: tail -f $APP_DIR/storage/logs/laravel.log"
echo "  - Restart services: sudo systemctl restart nginx php8.4-fpm"
echo "  - Clear cache: cd $APP_DIR && php artisan cache:clear"
echo ""
