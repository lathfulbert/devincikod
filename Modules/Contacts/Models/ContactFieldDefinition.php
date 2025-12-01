<?php

namespace Modules\Contacts\Models;

use App\Core\Database\Model;

class ContactFieldDefinition extends Model
{
    protected static string $table = 'contact_field_definitions';

    protected array $fillable = [
        'name',
        'slug',
        'type',
        'options',
        'is_required',
        'default_value',
        'sort_order',
        'placeholder',
        'help_text'
    ];

    /**
     * Get options as array
     */
    public function getOptions(): array
    {
        if (empty($this->options)) {
            return [];
        }

        $decoded = json_decode($this->options, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Set options
     */
    public function setOptions(array $options): void
    {
        $this->options = json_encode($options);
    }

    /**
     * Generate slug from name
     */
    public static function generateSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '_', $slug);
        $slug = trim($slug, '_');
        return $slug;
    }

    /**
     * Validate field value based on type
     */
    public function validateValue($value): bool
    {
        if ($this->is_required && empty($value)) {
            return false;
        }

        switch ($this->type) {
            case 'number':
                return is_numeric($value);
            case 'date':
                return strtotime($value) !== false;
            case 'select':
                $options = $this->getOptions();
                return in_array($value, $options);
            default:
                return true;
        }
    }
}
