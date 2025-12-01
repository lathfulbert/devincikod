<?php

namespace Modules\Contacts\Services;

use Modules\Contacts\Models\Contact;
use Modules\Contacts\Models\ContactFieldDefinition;

class FieldPersonalizationService
{
    /**
     * Replace placeholders in a message template with contact data
     *
     * @param string $template Message template with placeholders
     * @param Contact $contact Contact object
     * @return string Personalized message
     */
    public function personalize(string $template, Contact $contact): string
    {
        // Replace basic contact fields
        $message = $this->replaceBasicFields($template, $contact);

        // Replace custom fields
        $message = $this->replaceCustomFields($message, $contact);

        return $message;
    }

    /**
     * Replace basic contact field placeholders
     */
    protected function replaceBasicFields(string $template, Contact $contact): string
    {
        $replacements = [
            '{{first_name}}' => $contact->first_name ?? '',
            '{{last_name}}' => $contact->last_name ?? '',
            '{{full_name}}' => $contact->getFullName(),
            '{{phone}}' => $contact->phone ?? '',
            '{{email}}' => $contact->email ?? '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Replace custom field placeholders
     */
    protected function replaceCustomFields(string $template, Contact $contact): string
    {
        $customFields = $contact->getCustomFields();

        // Find all custom field placeholders
        preg_match_all('/\{\{custom\.([a-z0-9_]+)\}\}/', $template, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $slug) {
                $value = $customFields[$slug] ?? '';
                $template = str_replace("{{custom.{$slug}}}", $value, $template);
            }
        }

        return $template;
    }

    /**
     * Get all available placeholders for a message template
     *
     * @return array Array of placeholder information
     */
    public function getAvailablePlaceholders(): array
    {
        $placeholders = [
            'basic' => [
                '{{first_name}}' => 'Prénom du contact',
                '{{last_name}}' => 'Nom du contact',
                '{{full_name}}' => 'Nom complet',
                '{{phone}}' => 'Numéro de téléphone',
                '{{email}}' => 'Adresse email',
            ],
            'custom' => []
        ];

        // Get all defined custom fields
        $customFields = ContactFieldDefinition::query()
            ->orderBy('sort_order')
            ->get();

        foreach ($customFields as $field) {
            $placeholders['custom']["{{custom.{$field->slug}}}"] = $field->name;
        }

        return $placeholders;
    }

    /**
     * Preview personalized message for a contact
     *
     * @param string $template Message template
     * @param Contact $contact Contact to preview for
     * @return array ['original' => template, 'personalized' => result]
     */
    public function preview(string $template, Contact $contact): array
    {
        return [
            'original' => $template,
            'personalized' => $this->personalize($template, $contact),
            'placeholders_used' => $this->extractPlaceholders($template)
        ];
    }

    /**
     * Extract all placeholders from a template
     */
    public function extractPlaceholders(string $template): array
    {
        preg_match_all('/\{\{([^}]+)\}\}/', $template, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Validate if all placeholders in template are valid
     */
    public function validateTemplate(string $template): array
    {
        $placeholders = $this->extractPlaceholders($template);
        $available = $this->getAvailablePlaceholders();
        $valid = [];
        $invalid = [];

        foreach ($placeholders as $placeholder) {
            $fullPlaceholder = '{{' . $placeholder . '}}';

            if (isset($available['basic'][$fullPlaceholder])) {
                $valid[] = $placeholder;
            } elseif (isset($available['custom'][$fullPlaceholder])) {
                $valid[] = $placeholder;
            } else {
                $invalid[] = $placeholder;
            }
        }

        return [
            'valid' => $valid,
            'invalid' => $invalid,
            'is_valid' => empty($invalid)
        ];
    }
}
