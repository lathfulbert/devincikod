<?php

namespace Modules\Contacts\Models;

use App\Core\Database\Model;
use App\Core\Database\Traits\HasAuthor;
use App\Core\Database\Traits\SoftDeletes;

class Contact extends Model
{
    use HasAuthor;
    use SoftDeletes;
    protected static string $table = 'contacts';

    protected array $fillable = [
        'phone',
        'first_name',
        'last_name',
        'email',
        'custom_fields',
        'tags',
        'is_active'
    ];

    /**
     * Get custom field value by slug
     */
    public function getCustomField(string $slug, $default = null)
    {
        $customFields = $this->getCustomFields();
        return $customFields[$slug] ?? $default;
    }

    /**
     * Set custom field value
     */
    public function setCustomField(string $slug, $value): void
    {
        $customFields = $this->getCustomFields();
        $customFields[$slug] = $value;
        $this->custom_fields = json_encode($customFields);
    }

    /**
     * Get all custom fields as array
     */
    public function getCustomFields(): array
    {
        if (empty($this->custom_fields)) {
            return [];
        }

        $decoded = json_decode($this->custom_fields, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Set multiple custom fields
     */
    public function setCustomFields(array $fields): void
    {
        $this->custom_fields = json_encode($fields);
    }

    /**
     * Get tags as array
     */
    public function getTags(): array
    {
        if (empty($this->tags)) {
            return [];
        }

        $decoded = json_decode($this->tags, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Set tags
     */
    public function setTags(array $tags): void
    {
        $this->tags = json_encode($tags);
    }

    /**
     * Add a tag
     */
    public function addTag(string $tag): void
    {
        $tags = $this->getTags();
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->setTags($tags);
        }
    }

    /**
     * Get full name
     */
    public function getFullName(): string
    {
        return trim($this->first_name . ' ' . ($this->last_name ?? ''));
    }
}
