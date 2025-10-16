# 🚀 Deployment Guide

This guide covers deployment options for the TTS Translator application.

## Table of Contents
- [Pre-Deployment Checklist](#pre-deployment-checklist)
- [Platform-Specific Guides](#platform-specific-guides)
  - [Railway](#railway-deployment)
  - [Heroku](#heroku-deployment)
  - [DigitalOcean](#digitalocean-app-platform)
  - [VPS/Dedicated Server](#vpsdedicated-server)
- [Post-Deployment](#post-deployment)

---

## Pre-Deployment Checklist

Before deploying, ensure you have:

- [ ] Tested the application locally
- [ ] All features working correctly
- [ ] Database migrations ready
- [ ] Environment variables documented
- [ ] Frontend assets built (`npm run build`)
- [ ] Git repository ready (if using Git-based deployment)

---

## Platform-Specific Guides

### Railway Deployment

**Best for**: Quick deployment, automatic scaling, free tier available

#### Steps:

1. **Create Railway Account**
   - Visit https://railway.app
   - Sign up with GitHub

2. **Create New Project**
   ```bash
   # Initialize git repository (if not already)
   git init
   git add .
   git commit -m "Initial commit"
   ```

3. **Deploy from GitHub**
   - Click "New Project" in Railway
   - Select "Deploy from GitHub repo"
   - Choose your repository
   - Railway will auto-detect Laravel

4. **Add MySQL Database**
   - Click "New" → "Database" → "Add MySQL"
   - Railway will provide connection details

5. **Configure Environment Variables**
   ```
   APP_NAME=TTS Translator
   APP_ENV=production
   APP_KEY=(generate with: php artisan key:generate --show)
   APP_DEBUG=false
   APP_URL=https://your-app.railway.app
   
   DB_CONNECTION=mysql
   DB_HOST=(from Railway MySQL)
   DB_PORT=(from Railway MySQL)
   DB_DATABASE=(from Railway MySQL)
   DB_USERNAME=(from Railway MySQL)
   DB_PASSWORD=(from Railway MySQL)
   
   TTS_PROVIDER=web
   TRANSLATION_CACHE_ENABLED=true
   TRANSLATION_CACHE_TTL=86400
   ```

6. **Add Build Commands**
   - Build Command: `composer install && npm install && npm run build`
   - Start Command: `php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT`

7. **Deploy**
   - Railway will automatically deploy
   - Access your app at the provided URL

---

### Heroku Deployment

**Best for**: Established platform, easy scaling, add-ons ecosystem

#### Steps:

1. **Install Heroku CLI**
   ```bash
   # Linux/Mac
   curl https://cli-assets.heroku.com/install.sh | sh
   
   # Or visit: https://devcenter.heroku.com/articles/heroku-cli
   ```

2. **Login to Heroku**
   ```bash
   heroku login
   ```

3. **Create Heroku App**
   ```bash
   heroku create your-app-name
   ```

4. **Add ClearDB MySQL Add-on**
   ```bash
   heroku addons:create cleardb:ignite
   ```

5. **Get Database Credentials**
   ```bash
   heroku config:get CLEARDB_DATABASE_URL
   # Returns: mysql://username:password@host/database_name
   ```

6. **Set Environment Variables**
   ```bash
   heroku config:set APP_NAME="TTS Translator"
   heroku config:set APP_ENV=production
   heroku config:set APP_KEY=$(php artisan key:generate --show)
   heroku config:set APP_DEBUG=false
   heroku config:set LOG_CHANNEL=errorlog
   heroku config:set TTS_PROVIDER=web
   heroku config:set TRANSLATION_CACHE_ENABLED=true
   ```

7. **Configure Database from ClearDB URL**
   ```bash
   # Parse the CLEARDB_DATABASE_URL and set:
   heroku config:set DB_CONNECTION=mysql
   heroku config:set DB_HOST=<host-from-url>
   heroku config:set DB_PORT=3306
   heroku config:set DB_DATABASE=<database-from-url>
   heroku config:set DB_USERNAME=<username-from-url>
   heroku config:set DB_PASSWORD=<password-from-url>
   ```

8. **Create Procfile** (already included in project)
   ```
   web: vendor/bin/heroku-php-apache2 public/
   ```

9. **Deploy**
   ```bash
   git add .
   git commit -m "Ready for deployment"
   git push heroku main
   ```

10. **Run Migrations**
    ```bash
    heroku run php artisan migrate:fresh --seed --force
    heroku run php artisan storage:link
    ```

11. **Open App**
    ```bash
    heroku open
    ```

---

### DigitalOcean App Platform

**Best for**: DigitalOcean ecosystem, managed services, predictable pricing

#### Steps:

1. **Create DigitalOcean Account**
   - Visit https://www.digitalocean.com
   - Sign up for an account

2. **Create App from GitHub**
   - Go to App Platform
   - Click "Create App"
   - Connect GitHub repository
   - Select your repository and branch

3. **Configure App**
   - **Name**: tts-translator
   - **Region**: Choose closest to your users
   - **Plan**: Basic ($5/month recommended)

4. **Add Database**
   - Click "Add Resource" → "Database"
   - Select "MySQL"
   - Choose plan (Basic recommended)

5. **Environment Variables**
   Add in App Platform settings:
   ```
   APP_NAME=TTS Translator
   APP_ENV=production
   APP_KEY=base64:... (generate locally)
   APP_DEBUG=false
   DB_CONNECTION=mysql
   DB_HOST=${db.HOSTNAME}
   DB_PORT=${db.PORT}
   DB_DATABASE=${db.DATABASE}
   DB_USERNAME=${db.USERNAME}
   DB_PASSWORD=${db.PASSWORD}
   TTS_PROVIDER=web
   TRANSLATION_CACHE_ENABLED=true
   ```

6. **Build Configuration**
   - Build Command: `npm install && npm run build && composer install --no-dev`
   - Run Command: `php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8080`

7. **Deploy**
   - Click "Create Resources"
   - App Platform will build and deploy

---

### VPS/Dedicated Server

**Best for**: Full control, custom configuration, cost-effective for high traffic

#### Requirements:
- Ubuntu 20.04 or 22.04
- PHP 8.3+
- MySQL 8.0+
- Nginx or Apache
- Composer
- Node.js & NPM

#### Steps:

1. **Update System**
   ```bash
   sudo apt update && sudo apt upgrade -y
   ```

2. **Install PHP 8.3**
   ```bash
   sudo apt install -y software-properties-common
   sudo add-apt-repository ppa:ondrej/php
   sudo apt update
   sudo apt install -y php8.3 php8.3-fpm php8.3-mysql php8.3-xml php8.3-mbstring \
       php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl
   ```

3. **Install MySQL**
   ```bash
   sudo apt install -y mysql-server
   sudo mysql_secure_installation
   ```

4. **Install Nginx**
   ```bash
   sudo apt install -y nginx
   ```

5. **Install Composer**
   ```bash
   curl -sS https://getcomposer.org/installer | php
   sudo mv composer.phar /usr/local/bin/composer
   ```

6. **Install Node.js**
   ```bash
   curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
   sudo apt install -y nodejs
   ```

7. **Clone Repository**
   ```bash
   cd /var/www
   git clone <your-repository> tts-app
   cd tts-app
   ```

8. **Set Permissions**
   ```bash
   sudo chown -R www-data:www-data /var/www/tts-app
   sudo chmod -R 755 /var/www/tts-app
   sudo chmod -R 775 /var/www/tts-app/storage
   sudo chmod -R 775 /var/www/tts-app/bootstrap/cache
   ```

9. **Install Dependencies**
   ```bash
   composer install --no-dev --optimize-autoloader
   npm install
   npm run build
   ```

10. **Configure Environment**
    ```bash
    cp .env.example .env
    nano .env
    # Edit database credentials and other settings
    php artisan key:generate
    ```

11. **Run Migrations**
    ```bash
    php artisan migrate:fresh --seed --force
    php artisan storage:link
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    ```

12. **Configure Nginx**
    ```bash
    sudo nano /etc/nginx/sites-available/tts-app
    ```
    
    Add:
    ```nginx
    server {
        listen 80;
        listen [::]:80;
        server_name your-domain.com;
        root /var/www/tts-app/public;
    
        add_header X-Frame-Options "SAMEORIGIN";
        add_header X-Content-Type-Options "nosniff";
    
        index index.php;
    
        charset utf-8;
    
        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }
    
        location = /favicon.ico { access_log off; log_not_found off; }
        location = /robots.txt  { access_log off; log_not_found off; }
    
        error_page 404 /index.php;
    
        location ~ \.php$ {
            fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
            include fastcgi_params;
        }
    
        location ~ /\.(?!well-known).* {
            deny all;
        }
    }
    ```

13. **Enable Site**
    ```bash
    sudo ln -s /etc/nginx/sites-available/tts-app /etc/nginx/sites-enabled/
    sudo nginx -t
    sudo systemctl restart nginx
    ```

14. **Setup SSL (Optional but Recommended)**
    ```bash
    sudo apt install -y certbot python3-certbot-nginx
    sudo certbot --nginx -d your-domain.com
    ```

15. **Setup Cron Job for Cleanup (Optional)**
    ```bash
    sudo crontab -e
    ```
    
    Add:
    ```
    0 2 * * * cd /var/www/tts-app && php artisan schedule:run >> /dev/null 2>&1
    ```

---

## Post-Deployment

### 1. Verify Deployment
- [ ] App loads successfully
- [ ] Translation works
- [ ] Audio playback works
- [ ] History page accessible
- [ ] Dark mode toggles
- [ ] Copy/download functions work

### 2. Performance Optimization
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

### 3. Security
- [ ] HTTPS enabled
- [ ] APP_DEBUG=false
- [ ] Secure database credentials
- [ ] CSRF protection active
- [ ] Rate limiting configured

### 4. Monitoring
- Set up error logging
- Monitor server resources
- Track application performance
- Set up uptime monitoring

### 5. Backup
- Database backups (daily recommended)
- Code backups
- Environment file backups

---

## Troubleshooting

### Common Issues

**Issue**: 500 Internal Server Error
- Check storage permissions: `chmod -R 775 storage bootstrap/cache`
- Clear cache: `php artisan cache:clear`
- Check error logs: `tail -f storage/logs/laravel.log`

**Issue**: Database Connection Failed
- Verify database credentials in `.env`
- Check database server is running
- Ensure database exists

**Issue**: Assets Not Loading
- Run `npm run build`
- Check public folder permissions
- Verify APP_URL in `.env`

**Issue**: Translation Not Working
- Check internet connectivity
- Verify firewall allows outbound connections
- Check browser console for errors

---

## Support

For deployment assistance:
- Check Laravel documentation: https://laravel.com/docs
- Review application README.md
- Contact: your-email@example.com

---

**Last Updated**: October 2025

