<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'sort_order' => 0],
            ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Español', 'sort_order' => 1],
            ['code' => 'fr', 'name' => 'French', 'native_name' => 'Français', 'sort_order' => 2],
            ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch', 'sort_order' => 3],
            ['code' => 'it', 'name' => 'Italian', 'native_name' => 'Italiano', 'sort_order' => 4],
            ['code' => 'pt', 'name' => 'Portuguese', 'native_name' => 'Português', 'sort_order' => 5],
            ['code' => 'ja', 'name' => 'Japanese', 'native_name' => '日本語', 'sort_order' => 6],
            ['code' => 'zh', 'name' => 'Chinese', 'native_name' => '中文', 'sort_order' => 7],
            ['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية', 'sort_order' => 8],
            ['code' => 'ru', 'name' => 'Russian', 'native_name' => 'Русский', 'sort_order' => 9],
            ['code' => 'ko', 'name' => 'Korean', 'native_name' => '한국어', 'sort_order' => 10],
        ];

        foreach ($languages as $language) {
            Language::updateOrCreate(
                ['code' => $language['code']],
                $language
            );
        }
    }
}
