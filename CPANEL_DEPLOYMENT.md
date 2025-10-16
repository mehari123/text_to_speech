# 🚀 cPanel Deployment Guide

Complete guide to deploy the TTS Translator application from GitHub to cPanel hosting.

---

## 📋 Prerequisites

### On cPanel
- ✅ PHP 8.3 or higher
- ✅ MySQL/MariaDB database
- ✅ Composer installed
- ✅ Git deployment enabled
- ✅ Node.js (optional, for building assets)
- ✅ SSH access (recommended)

### On GitHub
- ✅ Repository created with this code
- ✅ Repository is public or cPanel has access

---

## 📁 Repository Structure

**Important**: The Git repository is in the root directory (`full_stack_dev`), and the Laravel app is in the `tts-app` subdirectory.

```
full_stack_dev/              ← Git repository root
├── .cpanel.yml             ← Deployment configuration
├── tts-app/                ← Laravel application
│   ├── app/
│   ├── public/
│   ├── resources/
│   ├── routes/
│   ├── database/
│   ├── composer.json
│   └── ...
├── DEVELOPMENT_PLAN.md
├── CPANEL_DEPLOYMENT.md    ← This file
└── requirement
```

---

## ⚙️ Step 1: Configure .cpanel.yml

The `.cpanel.yml` file is already created in the root directory. **You MUST update** the deployment path:

### Edit `.cpanel.yml`:

```yaml
---
deployment:
  tasks:
    # CHANGE THIS to your actual cPanel username and desired path
    - export DEPLOYPATH=/home/YOUR_CPANEL_USERNAME/public_html/
```

**Example**:
- If your cPanel username is `mysite`, use: `/home/mysite/public_html/`
- If deploying to a subdomain `app.mysite.com`, use: `/home/mysite/public_html/app/`
- If deploying to addon domain, use: `/home/mysite/public_html/yourdomain.com/`

### Important Notes:
1. The `.cpanel.yml` copies files from `tts-app/*` to your deployment path
2. It automatically runs composer install with production settings
3. It sets proper permissions for Laravel
4. It caches configuration for better performance

---

## 🗄️ Step 2: Create MySQL Database

### Via cPanel:

1. **Login to cPanel**
2. **Navigate to**: MySQL® Databases
3. **Create Database**:
   - Database name: `username_tts_translator`
   - Click "Create Database"

4. **Create Database User**:
   - Username: `username_tts_user`
   - Password: (generate strong password)
   - Click "Create User"

5. **Add User to Database**:
   - Select user and database
   - Grant **ALL PRIVILEGES**
   - Click "Make Changes"

6. **Note down**:
   ```
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=username_tts_translator
   DB_USERNAME=username_tts_user
   DB_PASSWORD=your_password_here
   ```

---

## 🔑 Step 3: Configure Environment Variables

### Via cPanel File Manager or SSH:

1. **Navigate to**: Your deployment directory (e.g., `/home/username/public_html/`)

2. **Edit `.env` file** (or create if doesn't exist):

```env
APP_NAME="TTS Translator"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=username_tts_translator
DB_USERNAME=username_tts_user
DB_PASSWORD=your_strong_password_here

SESSION_DRIVER=database
CACHE_STORE=file
QUEUE_CONNECTION=database

# TTS Configuration
TTS_PROVIDER=web
TRANSLATION_CACHE_ENABLED=true
TRANSLATION_CACHE_TTL=86400
```

3. **Generate APP_KEY** via SSH:
```bash
cd /home/username/public_html
php artisan key:generate
```

---

## 🌐 Step 4: Setup Git Deployment in cPanel

### Method 1: Git Version Control (Recommended)

1. **Login to cPanel**
2. **Navigate to**: Git™ Version Control
3. **Click**: "Create"

4. **Fill in details**:
   - **Clone URL**: `https://github.com/yourusername/your-repo.git`
   - **Repository Path**: `/home/username/repositories/tts-app`
   - **Repository Name**: `tts-translator`

5. **Click**: "Create"

6. **After cloning**, click "Manage" on the repository

7. **Enable**: "Deploy HEAD Commit"
   - Deployment Path: `/home/username/public_html/`
   - The `.cpanel.yml` file will handle the rest!

8. **Click**: "Update from Remote" to deploy

### Method 2: Manual Git via SSH

```bash
# SSH into your server
ssh username@yourdomain.com

# Navigate to web root
cd /home/username/public_html

# Clone repository
git clone https://github.com/yourusername/your-repo.git temp_repo

# Copy files from tts-app to current directory
cp -R temp_repo/tts-app/* ./

# Remove temp directory
rm -rf temp_repo

# Install dependencies
/usr/local/bin/ea-php83 /opt/cpanel/composer/bin/composer install --no-dev --optimize-autoloader

# Set permissions
chmod -R 755 storage bootstrap/cache
```

---

## 📦 Step 5: Post-Deployment Setup

### Via SSH (Recommended):

```bash
# Navigate to deployment directory
cd /home/username/public_html

# Run migrations and seed database
php artisan migrate:fresh --seed --force

# Create storage link
php artisan storage:link

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod -R 755 public

# Create audio storage directory
mkdir -p storage/app/public/audio
chmod -R 755 storage/app/public
```

### Via cPanel Terminal:

Same commands as above, accessible through cPanel → Terminal

---

## 🔧 Step 6: Configure .htaccess

### Create/Edit `.htaccess` in public_html:

**If deploying to root domain**:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Redirect to public folder
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ /public/$1 [L,QSA]
</IfModule>
```

**If deploying to subdomain/subdirectory**:

The Laravel `public/.htaccess` should work out of the box.

---

## 🌍 Step 7: Point Domain to Public Directory

### Method 1: Via cPanel (Best Practice)

1. **Navigate to**: Domains
2. **Find your domain**
3. **Click**: "Manage"
4. **Document Root**: Change to `/home/username/public_html/public`
5. **Save**

### Method 2: Via .htaccess (Alternative)

Keep the .htaccess rewrite rule from Step 6.

---

## ✅ Step 8: Verify Deployment

### Check these URLs:

1. **Homepage**: `https://yourdomain.com`
   - Should load the translation interface

2. **History**: `https://yourdomain.com/history`
   - Should show translation history page

3. **Test Translation**:
   - Enter text
   - Select languages
   - Translate
   - Test audio playback

### Troubleshooting:

**If you see "500 Internal Server Error"**:
```bash
# Check permissions
chmod -R 755 storage bootstrap/cache

# Clear cache
php artisan config:clear
php artisan cache:clear

# Check logs
tail -f storage/logs/laravel.log
```

**If database connection fails**:
```bash
# Test database connection
php artisan migrate:status

# Check .env file
cat .env | grep DB_
```

**If assets don't load**:
```bash
# Make sure assets are built
npm install
npm run build

# Or copy pre-built assets from your local machine
```

---

## 🔄 Updating the Application

### Automatic Updates (Git Deployment):

1. **Push changes** to your GitHub repository
2. **In cPanel** → Git™ Version Control
3. **Click** "Manage" on your repository
4. **Click** "Update from Remote"
5. The `.cpanel.yml` will automatically:
   - Copy files
   - Install dependencies
   - Cache configuration
   - Set permissions

### Manual Updates (SSH):

```bash
cd /home/username/public_html

# Pull latest changes
git pull origin main

# Update dependencies
/usr/local/bin/ea-php83 /opt/cpanel/composer/bin/composer install --no-dev

# Run migrations if needed
php artisan migrate --force

# Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Fix permissions
chmod -R 755 storage bootstrap/cache
```

---

## 🔐 Security Checklist

Before going live:

- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Set `APP_ENV=production` in `.env`
- [ ] Strong database password
- [ ] HTTPS enabled (SSL certificate installed)
- [ ] Proper file permissions (755 for directories, 644 for files)
- [ ] `.env` file not publicly accessible
- [ ] Git folder not in public directory
- [ ] `storage` and `bootstrap/cache` writable
- [ ] Error logging enabled
- [ ] CSRF protection active (Laravel default)

---

## 📊 Performance Optimization

### Enable OPcache (PHP):

In cPanel → MultiPHP INI Editor:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

### Enable Gzip Compression:

Add to `.htaccess`:
```apache
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/plain
  AddOutputFilterByType DEFLATE text/html
  AddOutputFilterByType DEFLATE text/css
  AddOutputFilterByType DEFLATE text/javascript
  AddOutputFilterByType DEFLATE application/javascript
  AddOutputFilterByType DEFLATE application/json
</IfModule>
```

### Browser Caching:

Add to `public/.htaccess`:
```apache
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType image/jpg "access 1 year"
  ExpiresByType image/jpeg "access 1 year"
  ExpiresByType image/gif "access 1 year"
  ExpiresByType image/png "access 1 year"
  ExpiresByType text/css "access 1 month"
  ExpiresByType application/javascript "access 1 month"
</IfModule>
```

---

## 🐛 Common Issues

### Issue: "Class not found"
**Solution**: Run `composer dump-autoload`

### Issue: "Permission denied"
**Solution**: 
```bash
chmod -R 755 storage bootstrap/cache
chown -R username:username storage bootstrap/cache
```

### Issue: "Database connection failed"
**Solution**: Verify database credentials in `.env`

### Issue: "Assets not loading"
**Solution**: 
- Check `APP_URL` in `.env`
- Ensure `public/build` folder exists
- Run `npm run build` locally and commit files

### Issue: "Session not working"
**Solution**: 
- Check `storage/framework/sessions` is writable
- Run `php artisan session:table` and `php artisan migrate`

---

## 📞 Support

### Useful Commands:

```bash
# Check PHP version
php -v

# Check Composer version
composer -V

# Check Laravel version
php artisan --version

# List all routes
php artisan route:list

# Check database connection
php artisan migrate:status

# View logs
tail -100 storage/logs/laravel.log

# Clear all caches
php artisan optimize:clear
```

---

## 🎉 Success!

If everything is set up correctly, you should have:

✅ Application accessible at your domain  
✅ Database connected and working  
✅ Translations working  
✅ Audio playback functional  
✅ History saving properly  
✅ HTTPS enabled  
✅ Fast loading times  

**Your TTS Translator is now live!** 🚀

---

## 📝 Quick Reference

### File Locations:
- **Application**: `/home/username/public_html/`
- **Public folder**: `/home/username/public_html/public/`
- **Environment file**: `/home/username/public_html/.env`
- **Logs**: `/home/username/public_html/storage/logs/`
- **Audio files**: `/home/username/public_html/storage/app/public/audio/`

### Important URLs:
- **cPanel**: `https://yourdomain.com:2083`
- **PHPMyAdmin**: cPanel → phpMyAdmin
- **Git Deployment**: cPanel → Git™ Version Control
- **File Manager**: cPanel → File Manager

---

*Last Updated: October 16, 2025*
*For cPanel hosting with Git deployment from GitHub*

