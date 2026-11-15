# Share Laragon Project on Local Network

## Quick Setup (3 Steps)

### Step 1: Configure Apache to Listen on All Interfaces

1. Open Laragon
2. Click **Menu** → **Apache** → **httpd.conf**
3. Find the line: `Listen 127.0.0.1:80`
4. Change it to: `Listen 0.0.0.0:80`
5. Save the file
6. Restart Apache in Laragon

### Step 2: Allow Through Windows Firewall

Right-click `share-localhost.bat` and select **Run as Administrator**

Or manually:
1. Open Windows Defender Firewall
2. Click "Allow an app through firewall"
3. Click "Change settings"
4. Find "Apache HTTP Server" and check both Private and Public
5. Click OK

### Step 3: Find Your IP and Share

Run in PowerShell:
```powershell
ipconfig | findstr IPv4
```

Your IP will be something like: `192.168.1.100` or `192.168.0.100`

Share this URL with friends:
```
http://YOUR_IP_ADDRESS/
```

For example: `http://192.168.1.100/`

---

## Alternative: Use Laragon's Share Feature

Laragon has a built-in sharing feature:

1. Right-click Laragon tray icon
2. Click **Share**
3. It will show you a public URL using ngrok (works over internet too!)

---

## For Your SMS App Specifically

If your project is at: `C:\laragon\www\Sale Management System\sms-app`

And you access it locally as: `http://sale.test`

Others will access it as:
```
http://YOUR_IP_ADDRESS/Sale%20Management%20System/sms-app/public/
```

Or create a virtual host for cleaner URL.

---

## Create Virtual Host for Network Access

1. Open Laragon
2. Click **Menu** → **Apache** → **sites-enabled** → **auto.sale.test.conf**
3. Add this line after `ServerName sale.test`:
   ```apache
   ServerAlias 192.168.*.* 
   ```
4. Save and restart Apache

Now others can access using your IP directly!

---

## Troubleshooting

### Can't access from other PC?

**Check 1: Firewall**
```powershell
# Run as Administrator
netsh advfirewall firewall add rule name="Apache" dir=in action=allow protocol=TCP localport=80
```

**Check 2: Apache is listening on all interfaces**
```powershell
netstat -an | findstr :80
```
Should show `0.0.0.0:80` not `127.0.0.1:80`

**Check 3: Same network**
Both PCs must be on same WiFi/network

**Check 4: Test from other PC**
```powershell
ping YOUR_IP_ADDRESS
Test-NetConnection -ComputerName YOUR_IP_ADDRESS -Port 80
```

---

## Security Note

When you open your localhost to the network:
- Only devices on your local WiFi can access it
- It's NOT accessible from the internet
- Turn off sharing when not needed
- Don't use this for production hosting

---

## Quick Commands

**Get your IP:**
```powershell
(Get-NetIPAddress -AddressFamily IPv4 | Where-Object {$_.IPAddress -like "192.168.*"}).IPAddress
```

**Test if port 80 is open:**
```powershell
Test-NetConnection -ComputerName localhost -Port 80
```

**Allow through firewall:**
```powershell
New-NetFirewallRule -DisplayName "Laragon Apache" -Direction Inbound -Protocol TCP -LocalPort 80 -Action Allow
```
