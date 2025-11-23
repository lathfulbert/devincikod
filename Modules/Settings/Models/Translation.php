<?php

namespace Modules\Settings\Models;

use App\Core\Database\Model;

class Translation extends Model
{
    protected static string $table = 'translations';
    protected array $fillable = ['language', 'key', 'value', 'module', 'updated_by'];

    /**
     * Get translation by key and language
     */
    public static function getTranslation(string $key, string $language = 'fr'): ?string
    {
        $translation = static::where('key', $key)
            ->where('language', $language)
            ->first();

        return $translation ? $translation->value : null;
    }

    /**
     * Get all translations for a language
     */
    public static function getByLanguage(string $language): array
    {
        $translations = static::where('language', $language)->get();
        $result = [];

        foreach ($translations as $translation) {
            $result[$translation->key] = $translation->value;
        }

        return $result;
    }

    /**
     * Set or update translation
     */
    public static function setTranslation(string $key, string $value, string $language = 'fr', string $module = 'general'): bool
    {
        $translation = static::where('key', $key)
            ->where('language', $language)
            ->first();

        $data = [
            'key' => $key,
            'value' => $value,
            'language' => $language,
            'module' => $module,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($translation) {
            return static::where('id', $translation->id)->update($data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            return static::create($data);
        }
    }

    /**
     * Get history of changes for a translation key
     */
    public function history()
    {
        return TranslationHistory::where('translation_id', $this->id)
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    /**
     * Save change to history before update
     */
    public function saveToHistory(int $userId): bool
    {
        if (!$this->id) {
            return false;
        }

        return TranslationHistory::create([
            'translation_id' => $this->id,
            'old_value' => $this->value,
            'new_value' => $this->value,
            'changed_by' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}
