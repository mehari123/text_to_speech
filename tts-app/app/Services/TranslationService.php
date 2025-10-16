<?php

namespace App\Services;

use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    protected $translator;
    protected $cacheEnabled;
    protected $cacheTtl;

    public function __construct()
    {
        $this->translator = new GoogleTranslate();
        $this->cacheEnabled = config('app.translation_cache_enabled', true);
        $this->cacheTtl = config('app.translation_cache_ttl', 86400); // 24 hours default
    }

    /**
     * Translate text from source language to target language.
     *
     * @param string $text
     * @param string $targetLanguage
     * @param string $sourceLanguage
     * @return string|null
     */
    public function translate(string $text, string $targetLanguage, string $sourceLanguage = 'en'): ?string
    {
        try {
            // Generate cache key
            $cacheKey = $this->getCacheKey($text, $targetLanguage, $sourceLanguage);

            // Try to get from cache first
            if ($this->cacheEnabled) {
                $cachedTranslation = Cache::get($cacheKey);
                if ($cachedTranslation) {
                    Log::info('Translation retrieved from cache', [
                        'target' => $targetLanguage,
                        'source' => $sourceLanguage
                    ]);
                    return $cachedTranslation;
                }
            }

            // Set source and target languages
            $this->translator->setSource($sourceLanguage);
            $this->translator->setTarget($targetLanguage);

            // Perform translation
            $translatedText = $this->translator->translate($text);

            // Cache the result
            if ($this->cacheEnabled && $translatedText) {
                Cache::put($cacheKey, $translatedText, $this->cacheTtl);
            }

            Log::info('Text translated successfully', [
                'target' => $targetLanguage,
                'source' => $sourceLanguage,
                'original_length' => strlen($text),
                'translated_length' => strlen($translatedText)
            ]);

            return $translatedText;

        } catch (\Exception $e) {
            Log::error('Translation failed', [
                'error' => $e->getMessage(),
                'target' => $targetLanguage,
                'source' => $sourceLanguage
            ]);
            
            return null;
        }
    }

    /**
     * Translate text to multiple languages.
     *
     * @param string $text
     * @param array $targetLanguages
     * @param string $sourceLanguage
     * @return array
     */
    public function translateMultiple(string $text, array $targetLanguages, string $sourceLanguage = 'en'): array
    {
        $translations = [];

        foreach ($targetLanguages as $language) {
            $translatedText = $this->translate($text, $language, $sourceLanguage);
            
            if ($translatedText) {
                $translations[$language] = $translatedText;
            }
        }

        return $translations;
    }

    /**
     * Detect the language of the given text.
     *
     * @param string $text
     * @return string|null
     */
    public function detectLanguage(string $text): ?string
    {
        try {
            return $this->translator->detect($text);
        } catch (\Exception $e) {
            Log::error('Language detection failed', [
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Generate cache key for translation.
     *
     * @param string $text
     * @param string $targetLanguage
     * @param string $sourceLanguage
     * @return string
     */
    protected function getCacheKey(string $text, string $targetLanguage, string $sourceLanguage): string
    {
        return 'translation_' . md5($text . $targetLanguage . $sourceLanguage);
    }

    /**
     * Clear translation cache.
     *
     * @return void
     */
    public function clearCache(): void
    {
        Cache::flush();
    }
}

