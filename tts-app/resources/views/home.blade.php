@extends('layouts.app')

@section('title', 'TTS Translator - Translate & Speak')

@section('content')
<div x-data="translatorApp()" x-cloak>
    <!-- Header Section -->
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-3">
            Text-to-Speech Translator
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-300">
            Translate your text to multiple languages and hear it spoken
        </p>
    </div>

    <!-- Main Translation Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden border border-gray-200 dark:border-gray-700">
        <div class="p-6 sm:p-8">
            <!-- Text Input Section -->
            <div class="mb-6">
                <label for="text-input" class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                    Enter your text (English)
                </label>
                <div class="relative">
                    <textarea
                        id="text-input"
                        x-model="inputText"
                        @input="updateCharCount()"
                        rows="5"
                        maxlength="5000"
                        placeholder="Type or paste your English text here..."
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white resize-none transition-colors"
                    ></textarea>
                    <div class="absolute bottom-3 right-3 flex items-center space-x-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400" x-text="charCount + ' / 5000'"></span>
                        <button
                            type="button"
                            @click="clearInput()"
                            x-show="inputText.length > 0"
                            class="text-gray-400 hover:text-red-500 transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Language Selection -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3">
                    Select target language(s)
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                    @foreach($languages as $language)
                    <label class="relative flex items-center cursor-pointer group">
                        <input
                            type="checkbox"
                            value="{{ $language->code }}"
                            x-model="selectedLanguages"
                            class="sr-only peer">
                        <div class="relative w-full px-4 py-3 rounded-lg border-2 border-gray-300 dark:border-gray-600 
                                    peer-checked:border-primary-600 peer-checked:bg-primary-100 dark:peer-checked:bg-primary-900/30 
                                    peer-checked:shadow-md peer-checked:scale-105
                                    hover:border-primary-400 dark:hover:border-primary-600 hover:shadow-sm
                                    transition-all duration-200 text-center">
                            <!-- Checkmark icon when selected -->
                            <div class="absolute top-2 right-2 hidden peer-checked:block">
                                <svg class="h-5 w-5 text-primary-600 dark:text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="text-sm font-medium text-gray-700 dark:text-gray-200 peer-checked:text-primary-700 dark:peer-checked:text-primary-300 peer-checked:font-bold">
                                {{ $language->name }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 peer-checked:text-primary-600 dark:peer-checked:text-primary-400">
                                {{ $language->native_name }}
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    Select one or more languages (max 10)
                </p>
            </div>

            <!-- Voice Settings (Collapsible) -->
            <div class="mb-6">
                <button
                    type="button"
                    @click="showVoiceSettings = !showVoiceSettings"
                    class="flex items-center justify-between w-full text-left text-sm font-semibold text-gray-700 dark:text-gray-200 mb-3 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                    <span>Voice Settings (Optional)</span>
                    <svg 
                        class="h-5 w-5 transform transition-transform" 
                        :class="showVoiceSettings ? 'rotate-180' : ''" 
                        fill="none" 
                        viewBox="0 0 24 24" 
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div 
                    x-show="showVoiceSettings" 
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform -translate-y-2"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Voice Gender
                        </label>
                        <select 
                            x-model="voiceSettings.gender"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                            <option value="female">Female</option>
                            <option value="male">Male</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Speed: <span x-text="voiceSettings.speed"></span>x
                        </label>
                        <input 
                            type="range" 
                            x-model="voiceSettings.speed" 
                            min="0.5" 
                            max="2" 
                            step="0.1"
                            class="w-full h-2 bg-gray-200 dark:bg-gray-600 rounded-lg appearance-none cursor-pointer accent-primary-600">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Pitch: <span x-text="voiceSettings.pitch"></span>
                        </label>
                        <input 
                            type="range" 
                            x-model="voiceSettings.pitch" 
                            min="0" 
                            max="2" 
                            step="0.1"
                            class="w-full h-2 bg-gray-200 dark:bg-gray-600 rounded-lg appearance-none cursor-pointer accent-primary-600">
                    </div>
                </div>
            </div>

            <!-- Translate Button -->
            <div class="flex justify-center">
                <button
                    type="button"
                    @click="translate()"
                    :disabled="loading || inputText.trim().length === 0 || selectedLanguages.length === 0"
                    class="inline-flex items-center px-8 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-gradient-to-r from-primary-600 to-purple-600 hover:from-primary-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                    <svg x-show="!loading" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                    </svg>
                    <svg x-show="loading" class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="loading ? 'Translating...' : 'Translate & Generate Speech'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Results Section -->
    <div 
        x-show="translations.length > 0" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-4"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        class="mt-8">
        
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
            Translation Results
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <template x-for="(translation, index) in translations" :key="index">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-shadow">
                    <div class="p-5">
                        <!-- Language Header -->
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 dark:bg-primary-900/30 text-primary-800 dark:text-primary-300">
                                    <span x-text="translation.language.name"></span>
                                </span>
                                <span class="text-sm text-gray-500 dark:text-gray-400" x-text="translation.language.native_name"></span>
                            </div>
                        </div>

                        <!-- Translated Text -->
                        <div class="mb-4">
                            <p class="text-gray-900 dark:text-white text-lg leading-relaxed" x-text="translation.translated_text"></p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center space-x-2">
                            <!-- Play Button -->
                            <button
                                type="button"
                                @click="speak(translation)"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Play
                            </button>

                            <!-- Copy Button -->
                            <button
                                type="button"
                                @click="copyText(translation.translated_text)"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Copy
                            </button>

                            <!-- Download Button (if audio available) -->
                            <button
                                type="button"
                                x-show="translation.audio_url"
                                @click="downloadAudio(translation.id)"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Download
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Recent Translations Section -->
    @if($recentTranslations && $recentTranslations->count() > 0)
    <div class="mt-12">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                Recent Translations
            </h2>
            <a href="{{ route('history') }}" class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                View All →
            </a>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($recentTranslations as $recent)
                <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-1">
                                <span class="text-xs font-medium text-primary-600 dark:text-primary-400">
                                    {{ $recent->language->name ?? $recent->target_language }}
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $recent->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-900 dark:text-white line-clamp-2">
                                {{ $recent->translated_text }}
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
function translatorApp() {
    return {
        inputText: '',
        selectedLanguages: [],
        voiceSettings: {
            gender: 'female',
            speed: 1.0,
            pitch: 1.0
        },
        showVoiceSettings: false, // Hidden by default
        charCount: 0,
        loading: false,
        translations: [],

        updateCharCount() {
            this.charCount = this.inputText.length;
        },

        clearInput() {
            this.inputText = '';
            this.charCount = 0;
        },

        async translate() {
            if (this.inputText.trim().length === 0) {
                showToast('Please enter some text to translate', 'error');
                return;
            }

            if (this.selectedLanguages.length === 0) {
                showToast('Please select at least one language', 'error');
                return;
            }

            this.loading = true;
            this.translations = [];

            try {
                const response = await fetch('/translate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        text: this.inputText,
                        languages: this.selectedLanguages,
                        voice_settings: this.voiceSettings
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.translations = data.translations;
                    showToast('Translation completed successfully!', 'success');
                } else {
                    showToast(data.message || 'Translation failed', 'error');
                }
            } catch (error) {
                console.error('Translation error:', error);
                showToast('An error occurred during translation', 'error');
            } finally {
                this.loading = false;
            }
        },

        speak(translation) {
            console.log('Attempting to speak:', translation.translated_text.substring(0, 50));
            
            // If audio URL is provided (server-generated audio), play it directly
            if (translation.audio_url) {
                console.log('Playing server-generated audio:', translation.audio_url);
                this.playAudioFile(translation.audio_url);
                return;
            }

            // Otherwise, use browser TTS as fallback
            console.log('No audio URL, using browser TTS');
            this.speakWithBrowserAPI(translation);
        },

        playAudioFile(audioUrl) {
            showToast('🔊 Playing audio...', 'info');
            
            const audio = new Audio(audioUrl);
            
            audio.onplay = () => {
                console.log('Audio playback started');
            };
            
            audio.onended = () => {
                console.log('Audio playback finished');
            };
            
            audio.onerror = (error) => {
                console.error('Audio playback error:', error);
                showToast('Audio playback failed', 'error');
            };
            
            audio.play().catch(error => {
                console.error('Failed to play audio:', error);
                showToast('Failed to play audio', 'error');
            });
        },

        speakWithBrowserAPI(translation) {
            console.log('Using browser Speech Synthesis API');
            
            if (!('speechSynthesis' in window)) {
                showToast('Text-to-speech not supported in your browser', 'error');
                return;
            }

            // Cancel any ongoing speech
            window.speechSynthesis.cancel();

            // Create utterance
            const text = translation.translated_text || translation;
            const langCode = translation.language ? translation.language.code : 'en';
            
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = this.getLanguageCode(langCode);
            utterance.rate = parseFloat(this.voiceSettings.speed);
            utterance.pitch = parseFloat(this.voiceSettings.pitch);
            utterance.volume = 1.0;

            console.log('Speaking with browser API:', {
                text: text.substring(0, 30) + '...',
                lang: utterance.lang,
                rate: utterance.rate,
                pitch: utterance.pitch
            });

            utterance.onstart = () => {
                console.log('Browser TTS started');
                showToast('🔊 Playing audio...', 'info');
            };

            utterance.onend = () => {
                console.log('Browser TTS finished');
            };

            utterance.onerror = (event) => {
                console.error('Browser TTS error:', event);
                showToast('Audio playback failed: ' + (event.error || 'Unknown error'), 'error');
            };

            // Load voices and speak
            const voices = window.speechSynthesis.getVoices();
            if (voices.length > 0) {
                const voice = voices.find(v => v.lang.startsWith(langCode)) || voices[0];
                utterance.voice = voice;
                console.log('Using voice:', voice.name, voice.lang);
            }

            // Speak
            window.speechSynthesis.speak(utterance);
            
            // Fallback if voices aren't loaded yet
            if (voices.length === 0) {
                console.log('Voices not loaded, waiting...');
                window.speechSynthesis.onvoiceschanged = () => {
                    const newVoices = window.speechSynthesis.getVoices();
                    const voice = newVoices.find(v => v.lang.startsWith(langCode)) || newVoices[0];
                    if (voice) {
                        utterance.voice = voice;
                        console.log('Loaded voice:', voice.name);
                    }
                };
            }
        },

        getResponsiveVoice(langCode) {
            // ResponsiveVoice voice names for different languages
            const gender = this.voiceSettings.gender === 'male' ? 'Male' : 'Female';
            
            const voiceMap = {
                'en': `UK English ${gender}`,
                'es': `Spanish ${gender}`,
                'fr': `French ${gender}`,
                'de': `Deutsch ${gender}`,
                'it': `Italian ${gender}`,
                'pt': `Brazilian Portuguese ${gender}`,
                'ja': 'Japanese Female',
                'zh': 'Chinese Female',
                'ar': 'Arabic Male',
                'ru': `Russian ${gender}`,
                'ko': 'Korean Female'
            };

            return voiceMap[langCode] || `UK English ${gender}`;
        },

        getLanguageCode(code) {
            const languageMap = {
                'en': 'en-US',
                'es': 'es-ES',
                'fr': 'fr-FR',
                'de': 'de-DE',
                'it': 'it-IT',
                'pt': 'pt-PT',
                'ja': 'ja-JP',
                'zh': 'zh-CN',
                'ar': 'ar-SA',
                'ru': 'ru-RU',
                'ko': 'ko-KR'
            };
            return languageMap[code] || code;
        },

        copyText(text) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('Text copied to clipboard!', 'success');
            }).catch(err => {
                showToast('Failed to copy text', 'error');
            });
        },

        downloadAudio(id) {
            window.location.href = `/download/${id}`;
            showToast('Downloading audio file...', 'info');
        }
    };
}

// Load voices when available
if ('speechSynthesis' in window) {
    window.speechSynthesis.onvoiceschanged = () => {
        window.speechSynthesis.getVoices();
    };
}
</script>
@endpush
@endsection

