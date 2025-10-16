<?php

namespace App\Http\Controllers;

use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HistoryController extends Controller
{
    /**
     * Display translation history page.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $perPage = 20;
        $languageFilter = $request->query('language');

        $query = Translation::with('language')
            ->latest();

        if ($languageFilter) {
            $query->where('target_language', $languageFilter);
        }

        $translations = $query->paginate($perPage);

        return view('history', compact('translations', 'languageFilter'));
    }

    /**
     * Get translation by ID (for replay).
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $translation = Translation::with('language')->findOrFail($id);

            return response()->json([
                'success' => true,
                'translation' => [
                    'id' => $translation->id,
                    'original_text' => $translation->original_text,
                    'translated_text' => $translation->translated_text,
                    'language' => [
                        'code' => $translation->target_language,
                        'name' => $translation->language->name ?? $translation->target_language,
                        'native_name' => $translation->language->native_name ?? '',
                    ],
                    'audio_url' => $translation->audio_url,
                    'voice_settings' => $translation->voice_settings,
                    'created_at' => $translation->created_at->format('Y-m-d H:i:s'),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve translation', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Translation not found',
            ], 404);
        }
    }

    /**
     * Delete translation from history.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $translation = Translation::findOrFail($id);

            // Delete audio file if exists
            if ($translation->audio_file_path) {
                $filePath = storage_path('app/public/' . $translation->audio_file_path);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $translation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Translation deleted successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete translation', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete translation',
            ], 500);
        }
    }

    /**
     * Clear all translation history.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear()
    {
        try {
            $translations = Translation::all();

            foreach ($translations as $translation) {
                if ($translation->audio_file_path) {
                    $filePath = storage_path('app/public/' . $translation->audio_file_path);
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            }

            Translation::truncate();

            return response()->json([
                'success' => true,
                'message' => 'History cleared successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to clear history', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear history',
            ], 500);
        }
    }
}
