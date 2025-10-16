<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\Translation;
use App\Services\TranslationService;
use App\Services\TextToSpeechService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TranslationController extends Controller
{
    protected $translationService;
    protected $ttsService;

    public function __construct(TranslationService $translationService, TextToSpeechService $ttsService)
    {
        $this->translationService = $translationService;
        $this->ttsService = $ttsService;
    }

    /**
     * Display the main translation page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $languages = Language::active()->ordered()->get();
        $recentTranslations = Translation::with('language')
            ->latest()
            ->limit(5)
            ->get();

        return view('home', compact('languages', 'recentTranslations'));
    }

    /**
     * Process translation request.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function translate(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:5000',
            'languages' => 'required|array|min:1|max:10',
            'languages.*' => 'required|string|exists:languages,code',
            'voice_settings' => 'nullable|array',
            'voice_settings.speed' => 'nullable|numeric|min:0.5|max:2',
            'voice_settings.pitch' => 'nullable|numeric|min:0|max:2',
            'voice_settings.gender' => 'nullable|string|in:male,female',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $originalText = $request->input('text');
            $targetLanguages = $request->input('languages');
            $voiceSettings = $request->input('voice_settings', []);
            
            $results = [];

            // Translate to each language
            foreach ($targetLanguages as $language) {
                // If target language is English, use original text
                if ($language === 'en') {
                    $translatedText = $originalText;
                } else {
                    // Translate text
                    $translatedText = $this->translationService->translate(
                        $originalText,
                        $language,
                        'en'
                    );

                    if (!$translatedText) {
                        Log::warning('Translation failed for language', ['language' => $language]);
                        continue;
                    }
                }

                // Generate audio (if not using web-based TTS)
                $audioPath = null;
                if ($this->ttsService->requiresServerGeneration()) {
                    $audioPath = $this->ttsService->generateSpeech(
                        $translatedText,
                        $language,
                        $voiceSettings
                    );
                }

                // Save to database
                $translation = Translation::create([
                    'original_text' => $originalText,
                    'translated_text' => $translatedText,
                    'source_language' => 'en',
                    'target_language' => $language,
                    'audio_file_path' => $audioPath,
                    'voice_settings' => $voiceSettings,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                // Get language info
                $languageInfo = Language::where('code', $language)->first();

                $results[] = [
                    'id' => $translation->id,
                    'language' => [
                        'code' => $language,
                        'name' => $languageInfo->name ?? $language,
                        'native_name' => $languageInfo->native_name ?? $language,
                    ],
                    'original_text' => $originalText,
                    'translated_text' => $translatedText,
                    'audio_url' => $audioPath ? asset('storage/' . $audioPath) : null,
                    'use_browser_tts' => !$this->ttsService->requiresServerGeneration(),
                    'voice_settings' => $voiceSettings,
                ];
            }

            if (empty($results)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Translation failed for all selected languages',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Translation completed successfully',
                'translations' => $results,
            ]);

        } catch (\Exception $e) {
            Log::error('Translation request failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during translation',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Download audio file.
     *
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
    public function download($id)
    {
        try {
            $translation = Translation::findOrFail($id);

            if (!$translation->audio_file_path) {
                return response()->json([
                    'success' => false,
                    'message' => 'No audio file available for this translation',
                ], 404);
            }

            $filePath = storage_path('app/public/' . $translation->audio_file_path);

            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Audio file not found',
                ], 404);
            }

            $language = Language::where('code', $translation->target_language)->first();
            $filename = 'translation_' . ($language->name ?? $translation->target_language) . '_' . time() . '.mp3';

            return response()->download($filePath, $filename);

        } catch (\Exception $e) {
            Log::error('Download failed', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Download failed',
            ], 500);
        }
    }
}
