#!/bin/bash

# Cloudflare Tunnel Setup for Debian
# This will expose your local app to the internet with a cloudflare URL

echo "=========================================="
echo "Cloudflare Tunnel Setup"
echo "=========================================="
echo ""

# Check if running on Debian
if [ ! -f /etc/debian_version ]; then
    echo "This script is for Debian/Ubuntu systems"
    exit 1
fi

# Install cloudflared
echo "Installing cloudflared..."
curl -L --output cloudflared.deb https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb
sudo dpkg -i cloudflared.deb
rm cloudflared.deb

# Verify installation
cloudflared --version

echo ""
echo "=========================================="
echo "Cloudflared installed successfully!"
echo "=========================================="
echo ""
echo "Next steps:"
echo ""
echo "1. Login to Cloudflare:"
echo "   cloudflared tunnel login"
echo ""
echo "2. Create a tunnel:"
echo "   cloudflared tunnel create sms-app"
echo ""
echo "3. Route your tunnel:"
echo "   cloudflared tunnel route dns sms-app sms-app.yourdomain.com"
echo ""
echo "4. Run the tunnel:"
echo "   cloudflared tunnel --url http://localhost:80 run sms-app"
echo ""
echo "Or use Quick Tunnel (no account needed):"
echo "   cloudflared tunnel --url http://localhost:80"
echo ""
