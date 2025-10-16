<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'original_text',
        'translated_text',
        'source_language',
        'target_language',
        'audio_file_path',
        'voice_settings',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'voice_settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the language this translation is in.
     */
    public function language()
    {
        return $this->belongsTo(Language::class, 'target_language', 'code');
    }

    /**
     * Get the audio URL for this translation.
     */
    public function getAudioUrlAttribute()
    {
        if (!$this->audio_file_path) {
            return null;
        }
        
        return asset('storage/' . $this->audio_file_path);
    }

    /**
     * Check if audio file exists.
     */
    public function hasAudio()
    {
        return !empty($this->audio_file_path);
    }

    /**
     * Scope a query to only include recent translations.
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->latest()->limit($limit);
    }

    /**
     * Scope a query to filter by language.
     */
    public function scopeByLanguage($query, $languageCode)
    {
        return $query->where('target_language', $languageCode);
    }
}
