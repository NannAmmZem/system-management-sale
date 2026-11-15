# Cloudflare Tunnel Setup Guide

Make your SMS app accessible globally with a free Cloudflare URL!

## Option 1: Quick Tunnel (No Account Needed - Easiest!)

### On Debian Server:

```bash
# Install cloudflared
curl -L --output cloudflared.deb https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb
sudo dpkg -i cloudflared.deb

# Start quick tunnel (generates random URL)
cloudflared tunnel --url http://localhost:80
```

You'll get a URL like: `https://random-words-123.trycloudflare.com`

**Pros:**
- No account needed
- Works immediately
- Free

**Cons:**
- Random URL each time
- URL changes when you restart
- Limited to testing

---

## Option 2: Named Tunnel (Permanent URL - Recommended!)

### Step 1: Create Cloudflare Account

1. Go to https://dash.cloudflare.com/sign-up
2. Create free account
3. (Optional) Add your domain or use Cloudflare's free subdomain

### Step 2: Install cloudflared on Debian

```bash
# Download and install
curl -L --output cloudflared.deb https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb
sudo dpkg -i cloudflared.deb

# Verify
cloudflared --version
```

### Step 3: Authenticate

```bash
cloudflared tunnel login
```

This will open a browser. Login to Cloudflare and authorize.

### Step 4: Create Tunnel

```bash
# Create tunnel named "sms-app"
cloudflared tunnel create sms-app

# This creates a credentials file
# Note the Tunnel ID shown
```

### Step 5: Configure Tunnel

```bash
# Create config file
sudo mkdir -p /etc/cloudflared
sudo nano /etc/cloudflared/config.yml
```

Add this content:

```yaml
tunnel: YOUR_TUNNEL_ID
credentials-file: /root/.cloudflared/YOUR_TUNNEL_ID.json

ingress:
  - hostname: sms-app.yourdomain.com
    service: http://localhost:80
  - service: http_status:404
```

Replace:
- `YOUR_TUNNEL_ID` with the ID from step 4
- `sms-app.yourdomain.com` with your desired subdomain

### Step 6: Route DNS

```bash
# If you have a domain in Cloudflare
cloudflared tunnel route dns sms-app sms-app.yourdomain.com

# Or use Cloudflare's free subdomain
# Go to Cloudflare dashboard → Zero Trust → Access → Tunnels
# Add public hostname there
```

### Step 7: Run Tunnel

```bash
# Test run
cloudflared tunnel run sms-app

# If it works, install as service
sudo cloudflared service install
sudo systemctl start cloudflared
sudo systemctl enable cloudflared

# Check status
sudo systemctl status cloudflared
```

### Step 8: Access Your App

Your app is now accessible at: `https://sms-app.yourdomain.com`

---

## Option 3: Use Cloudflare's Free Subdomain

If you don't have a domain:

1. Go to https://dash.cloudflare.com
2. Navigate to **Zero Trust** → **Access** → **Tunnels**
3. Click **Create a tunnel**
4. Name it "sms-app"
5. Install connector (follow their instructions)
6. Add public hostname:
   - Subdomain: `sms-app`
   - Domain: Choose from Cloudflare's free domains
   - Service: `http://localhost:80`
7. Save

You'll get: `https://sms-app.cfargotunnel.com` or similar

---

## Docker Integration

If using Docker, update the tunnel service:

```yaml
ingress:
  - hostname: sms-app.yourdomain.com
    service: http://localhost:80
  - service: http_status:404
```

The tunnel will forward to your Docker container on port 80.

---

## Troubleshooting

### Tunnel won't start

```bash
# Check logs
sudo journalctl -u cloudflared -f

# Check if port 80 is accessible
curl http://localhost:80
```

### Can't access URL

1. Check tunnel status: `sudo systemctl status cloudflared`
2. Verify DNS: `nslookup sms-app.yourdomain.com`
3. Check Cloudflare dashboard for tunnel status

### Update Laravel .env

```bash
cd /var/www/sms-app
nano .env
```

Update:
```env
APP_URL=https://sms-app.yourdomain.com
SESSION_SECURE_COOKIE=true
```

Then:
```bash
sudo docker compose exec app php artisan config:cache
sudo docker compose restart
```

---

## Security Considerations

✅ **Pros:**
- Free SSL/HTTPS automatically
- DDoS protection from Cloudflare
- No need to expose your home IP
- No port forwarding needed

⚠️ **Important:**
- Your app is now PUBLIC on the internet
- Add authentication/login
- Keep Laravel updated
- Monitor access logs
- Consider Cloudflare Access for additional security

---

## Quick Commands Reference

```bash
# Start tunnel manually
cloudflared tunnel run sms-app

# Start as service
sudo systemctl start cloudflared

# Stop service
sudo systemctl stop cloudflared

# View logs
sudo journalctl -u cloudflared -f

# List tunnels
cloudflared tunnel list

# Delete tunnel
cloudflared tunnel delete sms-app
```

---

## Alternative: ngrok (Simpler but Paid for Custom Domains)

```bash
# Install ngrok
curl -s https://ngrok-agent.s3.amazonaws.com/ngrok.asc | sudo tee /etc/apt/trusted.gpg.d/ngrok.asc >/dev/null
echo "deb https://ngrok-agent.s3.amazonaws.com buster main" | sudo tee /etc/apt/sources.list.d/ngrok.list
sudo apt update
sudo apt install ngrok

# Run (free random URL)
ngrok http 80

# With account (custom subdomain)
ngrok http 80 --subdomain=sms-app
```

---

## Cost Comparison

| Service | Free Tier | Custom Domain | SSL |
|---------|-----------|---------------|-----|
| Cloudflare Tunnel | ✅ Unlimited | ✅ Yes | ✅ Free |
| ngrok | ✅ Limited hours | ❌ Paid only | ✅ Free |
| Localhost.run | ✅ Yes | ❌ Random | ✅ Free |

**Recommendation:** Use Cloudflare Tunnel for production use!
