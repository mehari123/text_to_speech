@extends('layouts.app')

@section('title', 'Translation History - TTS Translator')

@section('content')
<div x-data="historyApp()">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Translation History</h1>
            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                View and manage your past translations
            </p>
        </div>
        
        @if($translations->count() > 0)
        <button
            type="button"
            @click="clearHistory()"
            class="inline-flex items-center px-4 py-2 border border-red-300 dark:border-red-700 text-sm font-medium rounded-md text-red-700 dark:text-red-400 bg-white dark:bg-gray-800 hover:bg-red-50 dark:hover:bg-red-900/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Clear All
        </button>
        @endif
    </div>

    @if($translations->count() > 0)
    <!-- Translations List -->
    <div class="space-y-4">
        @foreach($translations as $translation)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <!-- Language and Date -->
                        <div class="flex items-center space-x-3 mb-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary-100 dark:bg-primary-900/30 text-primary-800 dark:text-primary-300">
                                {{ $translation->language->name ?? $translation->target_language }}
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $translation->created_at->format('M d, Y - H:i') }}
                            </span>
                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                ({{ $translation->created_at->diffForHumans() }})
                            </span>
                        </div>

                        <!-- Original Text -->
                        <div class="mb-3">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">
                                Original (English)
                            </p>
                            <p class="text-sm text-gray-700 dark:text-gray-300 line-clamp-2">
                                {{ $translation->original_text }}
                            </p>
                        </div>

                        <!-- Translated Text -->
                        <div class="mb-4">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">
                                Translation
                            </p>
                            <p class="text-base text-gray-900 dark:text-white leading-relaxed">
                                {{ $translation->translated_text }}
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center space-x-2">
                            <!-- Play Button -->
                            <button
                                type="button"
                                @click="speak('{{ addslashes($translation->translated_text) }}', '{{ $translation->target_language }}')"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Play
                            </button>

                            <!-- Copy Button -->
                            <button
                                type="button"
                                @click="copyText('{{ addslashes($translation->translated_text) }}')"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Copy
                            </button>

                            @if($translation->audio_file_path)
                            <!-- Download Button -->
                            <a
                                href="{{ route('download', $translation->id) }}"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Download
                            </a>
                            @endif

                            <!-- Delete Button -->
                            <button
                                type="button"
                                @click="deleteTranslation({{ $translation->id }})"
                                class="inline-flex items-center px-3 py-2 border border-red-300 dark:border-red-700 text-sm font-medium rounded-md text-red-700 dark:text-red-400 bg-white dark:bg-gray-700 hover:bg-red-50 dark:hover:bg-red-900/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors ml-auto">
                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $translations->links() }}
    </div>

    @else
    <!-- Empty State -->
    <div class="text-center py-12">
        <svg class="mx-auto h-24 w-24 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No translation history</h3>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            Start by translating some text on the main page
        </p>
        <div class="mt-6">
            <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Go to Translator
            </a>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
function historyApp() {
    return {
        speak(text, languageCode) {
            // Use ResponsiveVoice (free TTS API) - more reliable
            if (typeof responsiveVoice !== 'undefined') {
                responsiveVoice.cancel();

                const voiceName = this.getResponsiveVoice(languageCode);
                
                const parameters = {
                    pitch: 1.0,
                    rate: 1.0,
                    onstart: () => {
                        showToast('🔊 Playing audio...', 'info');
                    },
                    onend: () => {
                        console.log('Speech finished');
                    },
                    onerror: (error) => {
                        console.error('Speech error:', error);
                        this.speakWithBrowserAPI(text, languageCode);
                    }
                };

                responsiveVoice.speak(text, voiceName, parameters);
            } else {
                this.speakWithBrowserAPI(text, languageCode);
            }
        },

        speakWithBrowserAPI(text, languageCode) {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();

                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = this.getLanguageCode(languageCode);
                utterance.rate = 1.0;
                utterance.pitch = 1.0;

                utterance.onstart = () => {
                    showToast('🔊 Playing audio...', 'info');
                };

                utterance.onerror = (event) => {
                    console.error('Browser TTS error:', event);
                    showToast('Audio playback failed. Please try again.', 'error');
                };

                window.speechSynthesis.speak(utterance);
            } else {
                showToast('Text-to-speech not supported in your browser', 'error');
            }
        },

        getResponsiveVoice(langCode) {
            const voiceMap = {
                'en': 'UK English Female',
                'es': 'Spanish Female',
                'fr': 'French Female',
                'de': 'Deutsch Female',
                'it': 'Italian Female',
                'pt': 'Brazilian Portuguese Female',
                'ja': 'Japanese Female',
                'zh': 'Chinese Female',
                'ar': 'Arabic Male',
                'ru': 'Russian Female',
                'ko': 'Korean Female'
            };

            return voiceMap[langCode] || 'UK English Female';
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

        async deleteTranslation(id) {
            if (!confirm('Are you sure you want to delete this translation?')) {
                return;
            }

            try {
                const response = await fetch(`/history/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showToast('Translation deleted successfully', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast(data.message || 'Failed to delete translation', 'error');
                }
            } catch (error) {
                console.error('Delete error:', error);
                showToast('An error occurred', 'error');
            }
        },

        async clearHistory() {
            if (!confirm('Are you sure you want to clear all translation history? This action cannot be undone.')) {
                return;
            }

            try {
                const response = await fetch('/history/clear', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showToast('History cleared successfully', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast(data.message || 'Failed to clear history', 'error');
                }
            } catch (error) {
                console.error('Clear history error:', error);
                showToast('An error occurred', 'error');
            }
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

