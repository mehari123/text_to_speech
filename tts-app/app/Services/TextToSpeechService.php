<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TextToSpeechService
{
    protected $provider;
    protected $apiKey;
    protected $elevenLabsApiKey;

    public function __construct()
    {
        $this->provider = config('app.tts_provider', 'web');
        $this->apiKey = config('app.voicerss_api_key');
        $this->elevenLabsApiKey = config('app.elevenlabs_api_key');
    }

    /**
     * Generate speech from text.
     *
     * @param string $text
     * @param string $language
     * @param array $voiceSettings
     * @return string|null The path to the generated audio file
     */
    public function generateSpeech(string $text, string $language, array $voiceSettings = []): ?string
    {
        // If provider is 'web', return null as we'll use browser-based TTS
        if ($this->provider === 'web') {
            return null; // Browser will handle TTS
        }

        // For ElevenLabs provider
        if ($this->provider === 'elevenlabs') {
            return $this->generateElevenLabs($text, $language, $voiceSettings);
        }

        // For VoiceRSS provider
        if ($this->provider === 'voicerss') {
            return $this->generateVoiceRSS($text, $language, $voiceSettings);
        }

        Log::warning('Unknown TTS provider', ['provider' => $this->provider]);
        return null;
    }

    /**
     * Generate speech using VoiceRSS API.
     *
     * @param string $text
     * @param string $language
     * @param array $voiceSettings
     * @return string|null
     */
    protected function generateVoiceRSS(string $text, string $language, array $voiceSettings = []): ?string
    {
        if (!$this->apiKey) {
            Log::error('VoiceRSS API key not configured');
            return null;
        }

        try {
            // Map language codes to VoiceRSS language codes
            $voiceLanguage = $this->mapLanguageToVoiceRSS($language);
            
            // Get voice settings
            $audioFormat = $voiceSettings['format'] ?? 'mp3';
            $audioCodec = $voiceSettings['codec'] ?? 'audio/mpeg';
            $rate = $voiceSettings['speed'] ?? '0'; // -10 to 10
            
            // Make request to VoiceRSS API
            $response = Http::get('https://api.voicerss.org/', [
                'key' => $this->apiKey,
                'src' => $text,
                'hl' => $voiceLanguage,
                'c' => $audioCodec,
                'f' => '44khz_16bit_stereo',
                'r' => $rate,
            ]);

            if ($response->successful()) {
                // Generate unique filename
                $filename = 'audio/' . Str::random(40) . '.' . $audioFormat;
                
                // Store the audio file
                Storage::disk('public')->put($filename, $response->body());
                
                Log::info('Audio generated successfully with VoiceRSS', [
                    'language' => $language,
                    'filename' => $filename
                ]);
                
                return $filename;
            }

            Log::error('VoiceRSS API request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            return null;

        } catch (\Exception $e) {
            Log::error('TTS generation failed', [
                'error' => $e->getMessage(),
                'language' => $language
            ]);
            
            return null;
        }
    }

    /**
     * Generate speech using ElevenLabs API.
     *
     * @param string $text
     * @param string $language
     * @param array $voiceSettings
     * @return string|null
     */
    protected function generateElevenLabs(string $text, string $language, array $voiceSettings = []): ?string
    {
        if (!$this->elevenLabsApiKey) {
            Log::error('ElevenLabs API key not configured');
            return null;
        }

        try {
            // Get the appropriate voice ID for the language
            $voiceId = $this->getElevenLabsVoiceId($language, $voiceSettings['gender'] ?? 'female');
            
            // Prepare voice settings
            $stability = 0.5;
            $similarity_boost = 0.75;
            
            // Make request to ElevenLabs API
            $response = Http::withHeaders([
                'xi-api-key' => $this->elevenLabsApiKey,
                'Content-Type' => 'application/json',
            ])->post("https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}", [
                'text' => $text,
                'model_id' => 'eleven_multilingual_v2',
                'voice_settings' => [
                    'stability' => $stability,
                    'similarity_boost' => $similarity_boost,
                ]
            ]);

            if ($response->successful()) {
                // Generate unique filename
                $filename = 'audio/' . Str::random(40) . '.mp3';
                
                // Store the audio file
                Storage::disk('public')->put($filename, $response->body());
                
                Log::info('Audio generated successfully with ElevenLabs', [
                    'language' => $language,
                    'voice_id' => $voiceId,
                    'filename' => $filename,
                    'text_length' => strlen($text)
                ]);
                
                return $filename;
            }

            Log::error('ElevenLabs API request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            return null;

        } catch (\Exception $e) {
            Log::error('ElevenLabs TTS generation failed', [
                'error' => $e->getMessage(),
                'language' => $language
            ]);
            
            return null;
        }
    }

    /**
     * Get ElevenLabs voice ID for language and gender.
     *
     * @param string $language
     * @param string $gender
     * @return string
     */
    protected function getElevenLabsVoiceId(string $language, string $gender = 'female'): string
    {
        // ElevenLabs pre-made voices
        // You can get more voice IDs from: https://api.elevenlabs.io/v1/voices
        
        $voices = [
            // English voices
            'en_female' => 'EXAVITQu4vr4xnSDxMaL', // Bella
            'en_male' => 'TxGEqnHWrfWFTfGW9XjX', // Josh
            
            // Multilingual voices (work for all languages)
            'female' => 'Xb7hH8MSUJpSbSDYk0k2', // Alice (multilingual)
            'male' => 'pNInz6obpgDQGcFmaJgB', // Adam (multilingual)
        ];

        // Try to get language-specific voice first
        $key = "{$language}_{$gender}";
        if (isset($voices[$key])) {
            return $voices[$key];
        }

        // Fallback to generic multilingual voice
        return $voices[$gender] ?? $voices['female'];
    }

    /**
     * Map language code to VoiceRSS language code.
     *
     * @param string $language
     * @return string
     */
    protected function mapLanguageToVoiceRSS(string $language): string
    {
        $languageMap = [
            'en' => 'en-us',
            'es' => 'es-es',
            'fr' => 'fr-fr',
            'de' => 'de-de',
            'it' => 'it-it',
            'pt' => 'pt-pt',
            'ja' => 'ja-jp',
            'zh' => 'zh-cn',
            'ar' => 'ar-sa',
            'ru' => 'ru-ru',
            'ko' => 'ko-kr',
        ];

        return $languageMap[$language] ?? 'en-us';
    }

    /**
     * Delete audio file.
     *
     * @param string $filePath
     * @return bool
     */
    public function deleteAudio(string $filePath): bool
    {
        try {
            if (Storage::disk('public')->exists($filePath)) {
                return Storage::disk('public')->delete($filePath);
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete audio file', [
                'file' => $filePath,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Clean up old audio files (older than specified days).
     *
     * @param int $days
     * @return int Number of files deleted
     */
    public function cleanupOldAudio(int $days = 7): int
    {
        try {
            $files = Storage::disk('public')->files('audio');
            $deletedCount = 0;
            $cutoffTime = now()->subDays($days)->timestamp;

            foreach ($files as $file) {
                if (Storage::disk('public')->lastModified($file) < $cutoffTime) {
                    Storage::disk('public')->delete($file);
                    $deletedCount++;
                }
            }

            Log::info('Cleaned up old audio files', [
                'deleted_count' => $deletedCount,
                'days' => $days
            ]);

            return $deletedCount;

        } catch (\Exception $e) {
            Log::error('Audio cleanup failed', [
                'error' => $e->getMessage()
            ]);
            
            return 0;
        }
    }

    /**
     * Get TTS provider name.
     *
     * @return string
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * Check if provider requires server-side generation.
     *
     * @return bool
     */
    public function requiresServerGeneration(): bool
    {
        return $this->provider !== 'web';
    }
}

