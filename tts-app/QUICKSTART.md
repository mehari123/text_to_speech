# ⚡ Quick Start Guide

Get the TTS Translator up and running in **5 minutes**!

## Prerequisites

- ✅ PHP 8.3+ installed
- ✅ MySQL installed and running
- ✅ Composer installed
- ✅ Node.js & NPM installed

## Installation (5 Steps)

### 1️⃣ Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 2️⃣ Setup Database

```bash
# Create MySQL database
sudo mysql -e "CREATE DATABASE tts_translator;"
sudo mysql -e "CREATE USER 'tts_user'@'localhost' IDENTIFIED BY 'tts_password_123';"
sudo mysql -e "GRANT ALL PRIVILEGES ON tts_translator.* TO 'tts_user'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"
```

### 3️⃣ Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tts_translator
DB_USERNAME=tts_user
DB_PASSWORD=tts_password_123
```

### 4️⃣ Setup Application

```bash
# Run migrations and seed database
php artisan migrate:fresh --seed

# Create storage link
php artisan storage:link

# Build frontend assets
npm run build
```

### 5️⃣ Start Server

```bash
# Start Laravel development server
php artisan serve
```

**🎉 Done!** Visit: **http://localhost:8000**

---

## Quick Test

1. **Enter Text**: "Hello, how are you today?"
2. **Select Languages**: Check "Spanish", "French", "German"
3. **Click**: "Translate & Generate Speech"
4. **Play Audio**: Click the play button on any translation
5. **Success!** 🎉

---

## Troubleshooting

### Database Connection Error

```bash
# Check MySQL is running
sudo systemctl status mysql

# Clear config cache
php artisan config:clear

# Verify database credentials in .env
```

### Assets Not Building

```bash
# Clear npm cache
npm cache clean --force

# Reinstall dependencies
rm -rf node_modules
npm install

# Try building again
npm run build
```

### Permission Errors

```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache

# If needed, change ownership
sudo chown -R $USER:www-data storage bootstrap/cache
```

---

## Production Deployment

For production deployment, see **[DEPLOYMENT.md](DEPLOYMENT.md)** for detailed guides on:
- Railway
- Heroku
- DigitalOcean
- VPS/Dedicated Server

---

## What's Next?

- ✅ Read full [README.md](README.md) for detailed documentation
- ✅ Check [TESTING.md](TESTING.md) for testing guide
- ✅ Review [DEPLOYMENT.md](DEPLOYMENT.md) for deployment options
- ✅ Explore the features and customize as needed

---

## Support

Need help? Check the documentation or open an issue!

**Built with ❤️ using Laravel & Tailwind CSS**

