<?php

namespace Modules\Settings\Models;

use App\Core\Database\Model;

class TranslationHistory extends Model
{
    protected static string $table = 'translation_history';
    protected array $fillable = ['translation_id', 'old_value', 'new_value', 'changed_by'];

    /**
     * Get the user who made the change
     */
    public function user()
    {
        return \Modules\Users\Models\User::find($this->changed_by);
    }

    /**
     * Get the translation
     */
    public function translation()
    {
        return Translation::find($this->translation_id);
    }
}
