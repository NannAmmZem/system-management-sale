#!/bin/bash

# Docker deployment script for Debian server

echo "=========================================="
echo "Docker Deployment for SMS App"
echo "=========================================="
echo ""

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "Docker not found. Installing Docker..."
    
    # Install Docker
    sudo apt update
    sudo apt install -y ca-certificates curl gnupg
    sudo install -m 0755 -d /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/debian/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    sudo chmod a+r /etc/apt/keyrings/docker.gpg
    
    echo \
      "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/debian \
      $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
      sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
    
    sudo apt update
    sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
    
    # Add user to docker group
    sudo usermod -aG docker $USER
    
    echo "Docker installed successfully!"
    echo "Please log out and log back in for group changes to take effect."
    echo "Then run this script again."
    exit 0
fi

# Check if Docker Compose is installed
if ! command -v docker compose &> /dev/null; then
    echo "Docker Compose not found. Installing..."
    sudo apt install -y docker-compose-plugin
fi

echo "Docker is installed. Proceeding with deployment..."
echo ""

# Stop existing Nginx and PHP-FPM to free port 80
echo "Stopping existing web services..."
sudo systemctl stop nginx
sudo systemctl stop php8.4-fpm
sudo systemctl disable nginx
sudo systemctl disable php8.4-fpm

# Navigate to app directory
cd /var/www/sms-app

# Create .env if it doesn't exist
if [ ! -f .env ]; then
    cp .env.example .env
    echo "APP_KEY=" >> .env
fi

# Ensure database exists
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
fi

# Build and start containers
echo ""
echo "Building Docker image..."
docker compose build

echo ""
echo "Starting containers..."
docker compose up -d

# Wait for container to be ready
echo ""
echo "Waiting for container to start..."
sleep 5

# Run Laravel setup inside container
echo ""
echo "Setting up Laravel..."
docker compose exec -T app php artisan key:generate --force
docker compose exec -T app php artisan migrate --force
docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache
docker compose exec -T app php artisan view:cache

# Fix permissions
docker compose exec -T app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
docker compose exec -T app chmod -R 775 /var/www/storage /var/www/bootstrap/cache

echo ""
echo "=========================================="
echo "Deployment Complete!"
echo "=========================================="
echo ""
echo "Your application is now running in Docker!"
echo "Access it at: http://192.168.233.100"
echo ""
echo "Useful Docker commands:"
echo "  - View logs: docker compose logs -f"
echo "  - Stop: docker compose stop"
echo "  - Start: docker compose start"
echo "  - Restart: docker compose restart"
echo "  - Remove: docker compose down"
echo ""
