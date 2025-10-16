-- Create MySQL user for the application
CREATE USER IF NOT EXISTS 'tts_user'@'localhost' IDENTIFIED BY 'tts_password_123';
GRANT ALL PRIVILEGES ON tts_translator.* TO 'tts_user'@'localhost';
FLUSH PRIVILEGES;
