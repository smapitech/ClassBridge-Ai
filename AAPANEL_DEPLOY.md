# ClassBridge AI - aaPanel Production Deployment Guide

## Website & Database Info

| Setting | Value |
|---------|-------|
| **Domain** | `aistackapp.site` |
| **Root Directory** | `/www/wwwroot/aistackapp.site` |
| **DB Name** | `sql_aistackapp_site` |
| **DB User** | `sql_aistackapp_site` |
| **DB Password** | `c0ee1265efd33` |

---

## Prerequisites
- aaPanel installed on CentOS/AlmaLinux/Ubuntu
- PHP 8.2+ with extensions: `bcmath`, `ctype`, `curl`, `fileinfo`, `gd`, `json`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`, `redis`
- MySQL 5.7+ or MariaDB 10.5+
- Composer 2.x
- Node.js 18+ (for frontend build)

---

## Step 1: Upload Files to Server

1. On your local machine, zip the project (exclude: `.git`, `node_modules`, `vendor`, `storage/logs/*`)
2. Upload zip via **aaPanel File Manager** to `/www/wwwroot/aistackapp.site`
3. Unzip the project

```bash
cd /www/wwwroot/aistackapp.site
chown -R www:www .
```

---

## Step 2: Create Database in aaPanel

1. Go to **aaPanel → Database → Add Database**
2. Database name: `sql_aistackapp_site`
3. Username: `sql_aistackapp_site`
4. Password: `c0ee1265efd33`

---

## Step 3: Configure .env

The `.env.production` file is pre-configured with the correct values:

```bash
cd /www/wwwroot/aistackapp.site
cp .env.production .env
```

No manual editing needed — all values are pre-set for `aistackapp.site`.

---

## Step 4: Install Dependencies & Build

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force
```

---

## Step 5: Cache for Production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## Step 6: Create Website in aaPanel

1. **aaPanel → Website → Add Site**
2. Domain: `aistackapp.site`
3. PHP version: **8.2+**
4. Root directory: `/www/wwwroot/aistackapp.site/public`

---

## Step 7: SSL (aaPanel)

**Website → aistackapp.site → SSL → Let's Encrypt → Apply**

---

## Step 8: Nginx Configuration

Add to site config in aaPanel → Website → aistackapp.site → Config:

```nginx
server {
    listen 80;
    server_name aistackapp.site;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name aistackapp.site;
    
    ssl_certificate /www/server/panel/vhost/cert/aistackapp.site/fullchain.pem;
    ssl_certificate_key /www/server/panel/vhost/cert/aistackapp.site/privkey.pem;
    
    root /www/wwwroot/aistackapp.site/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/tmp/php-cgi-82.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Reverb WebSocket
    location /app {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
    
    location /apps {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
    
    location ~ /\.(?!well-known) {
        deny all;
    }
}
```

---

## Step 9: Supervisor (Queue + Reverb)

Install Supervisor from aaPanel **App Store → Supervisor**.

### Queue Worker:
- **Name:** `classbridge-queue`
- **Directory:** `/www/wwwroot/aistackapp.site`
- **Command:** `php artisan queue:work --sleep=3 --tries=3 --timeout=300`
- **Processes:** 3

### Reverb Server:
- **Name:** `classbridge-reverb`
- **Directory:** `/www/wwwroot/aistackapp.site`
- **Command:** `php artisan reverb:start --host=0.0.0.0 --port=8080`
- **Processes:** 1

### Scheduler:
- **Name:** `classbridge-scheduler`
- **Directory:** `/www/wwwroot/aistackapp.site`
- **Command:** `php artisan schedule:work`
- **Processes:** 1

---

## Step 10: Set Permissions

```bash
cd /www/wwwroot/aistackapp.site
chmod -R 775 storage bootstrap/cache
chown -R www:www storage bootstrap/cache
```

---

## Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@classbridge.test | password |
| School Owner | owner@demoacademy.com | password |
| School Admin | principal@demoacademy.com | password |
| Teacher | teacher@demoacademy.com | password |
| Student | student@demoacademy.com | password |
| Parent | parent@demoacademy.com | password |

---

## Troubleshooting

### 500 error:
```bash
php artisan optimize:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

### Database connection fails:
```bash
# Verify MySQL is running
systemctl status mysqld
# Test connection
mysql -u sql_aistackapp_site -p sql_aistackapp_site
```

### Reverb not connecting:
```bash
supervisorctl status classbridge-reverb
netstat -tlnp | grep 8080