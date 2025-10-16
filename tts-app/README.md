# 🎙️ Text-to-Speech Translator

A modern, full-stack web application that translates English text into multiple languages and converts the translations to speech using browser-based Text-to-Speech technology.

![Laravel](https://img.shields.io/badge/Laravel-12.x-red)
![PHP](https://img.shields.io/badge/PHP-8.3+-blue)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.x-cyan)
![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-green)

## ✨ Features

### Core Features
- ✅ **Text Translation**: Translate English text to 10+ languages
- ✅ **Text-to-Speech**: Browser-based TTS with natural voices
- ✅ **Multi-Language Support**: Spanish, French, German, Italian, Portuguese, Japanese, Chinese, Arabic, Russian, Korean
- ✅ **Real-time Translation**: Fast translation using Google Translate API
- ✅ **Audio Playback**: Play translated text directly in the browser

### Bonus Features
- ✅ **Voice Settings**: Customize gender, speed, and pitch
- ✅ **Download Audio**: Download generated audio files (when server-side TTS is enabled)
- ✅ **Translation History**: View and manage past translations
- ✅ **Modern UI**: Beautiful, responsive interface with Tailwind CSS
- ✅ **Dark Mode**: Toggle between light and dark themes
- ✅ **Copy to Clipboard**: Quick copy translated text
- ✅ **Error Handling**: Friendly error messages and validation
- ✅ **Caching**: Translation caching for improved performance
- ✅ **Character Counter**: Real-time character count (max 5000)

## 🛠️ Tech Stack

**Backend:**
- Laravel 12.x
- PHP 8.3+
- MySQL Database
- Google Translate PHP Library

**Frontend:**
- Tailwind CSS
- Alpine.js
- Vite
- Web Speech API

**Additional:**
- Browser-based TTS (no API key required)
- Optional: VoiceRSS API support

## 📋 Requirements

- PHP 8.3 or higher
- Composer
- Node.js & NPM
- MySQL 8.0+
- Web browser with Speech Synthesis support

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd tts-app
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 3. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Database

Edit `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tts_translator
DB_USERNAME=tts_user
DB_PASSWORD=tts_password_123
```

Create MySQL database and user:

```sql
CREATE DATABASE tts_translator CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'tts_user'@'localhost' IDENTIFIED BY 'tts_password_123';
GRANT ALL PRIVILEGES ON tts_translator.* TO 'tts_user'@'localhost';
FLUSH PRIVILEGES;
```

### 5. Run Migrations

```bash
php artisan migrate:fresh --seed
```

### 6. Create Storage Link

```bash
php artisan storage:link
```

### 7. Build Frontend Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 8. Start Development Server

```bash
php artisan serve
```

Visit: **http://localhost:8000**

## 📖 Usage

### Translating Text

1. **Enter Text**: Type or paste your English text (up to 5000 characters)
2. **Select Languages**: Choose one or more target languages
3. **Adjust Voice Settings** (Optional): Configure gender, speed, and pitch
4. **Translate**: Click "Translate & Generate Speech"
5. **Listen**: Click the "Play" button to hear the translation
6. **Copy/Download**: Use the buttons to copy text or download audio

### Viewing History

- Navigate to the **History** page from the menu
- View all past translations
- Play, copy, or delete individual translations
- Clear all history with one click

### Dark Mode

- Click the moon/sun icon in the navigation bar
- Theme preference is saved in localStorage

## 🎨 Screenshots

### Main Translation Page
![Main Page](docs/screenshots/main-page.png)

### Translation Results
![Results](docs/screenshots/results.png)

### History Page
![History](docs/screenshots/history.png)

## 🔧 Configuration

### Translation Settings

Edit `.env`:

```env
# Enable translation caching
TRANSLATION_CACHE_ENABLED=true

# Cache TTL (in seconds)
TRANSLATION_CACHE_TTL=86400
```

### Text-to-Speech Provider

```env
# Options: 'web' (browser-based, free) or 'voicerss' (API-based)
TTS_PROVIDER=web

# VoiceRSS API Key (optional, only if using VoiceRSS)
VOICERSS_API_KEY=your_api_key_here
```

## 📁 Project Structure

```
tts-app/
├── app/
│   ├── Http/Controllers/
│   │   ├── TranslationController.php
│   │   └── HistoryController.php
│   ├── Models/
│   │   ├── Language.php
│   │   └── Translation.php
│   └── Services/
│       ├── TranslationService.php
│       └── TextToSpeechService.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   │   ├── layouts/app.blade.php
│   │   ├── home.blade.php
│   │   └── history.blade.php
│   ├── css/app.css
│   └── js/app.js
├── routes/
│   └── web.php
└── public/
```

## 🚢 Deployment

### Production Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Configure production database
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Run `npm run build`
- [ ] Set proper file permissions
- [ ] Configure web server (Nginx/Apache)
- [ ] Enable HTTPS
- [ ] Set up cron for cleanup (optional)

### Deployment Platforms

#### Railway
1. Connect GitHub repository
2. Add MySQL database
3. Set environment variables
4. Deploy automatically

#### Heroku
1. Create new app
2. Add ClearDB MySQL add-on
3. Configure buildpacks
4. Push to Heroku

#### DigitalOcean
1. Create App Platform app
2. Add managed database
3. Configure environment
4. Deploy

### Web Server Configuration

#### Nginx

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/tts-app/public;

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

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter TranslationTest
```

## 🔒 Security

- CSRF protection enabled
- XSS protection with Blade templates
- SQL injection protection with Eloquent ORM
- Rate limiting on API endpoints
- Validated user inputs
- Secure password handling

## 🐛 Troubleshooting

### Translation not working
- Check internet connection
- Verify Google Translate service is accessible
- Check browser console for errors

### Audio not playing
- Ensure browser supports Web Speech API
- Check browser permissions for audio
- Try different browser (Chrome/Edge recommended)

### Database connection errors
- Verify MySQL is running
- Check database credentials in `.env`
- Ensure database exists
- Run `php artisan config:clear`

## 📝 API Endpoints

```
GET  /                  - Main translation page
POST /translate         - Process translation
GET  /download/{id}     - Download audio file
GET  /history           - Translation history
GET  /history/{id}      - Get specific translation
DELETE /history/{id}    - Delete translation
POST /history/clear     - Clear all history
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

This project is open-sourced software licensed under the MIT license.

## 👨‍💻 Author

Built with ❤️ for the Full Stack Developer Test

## 🙏 Acknowledgments

- Laravel Framework
- Google Translate (via Stichoza package)
- Tailwind CSS
- Alpine.js
- Web Speech API

## 📞 Support

For support, please open an issue in the repository.

---

**Made with Laravel 12 & Tailwind CSS**
