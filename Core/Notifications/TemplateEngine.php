<?php

namespace App\Core\Notifications;

use Modules\Notifications\Models\NotificationTemplate;

/**
 * Template Engine
 * 
 * Renders notification templates with variable substitution
 */
class TemplateEngine
{
    /**
     * Render template with data
     */
    public function render(string $templateName, string $channel, array $data): array
    {
        $template = NotificationTemplate::findActive($templateName, $channel);

        if (!$template) {
            throw new \RuntimeException("Template '{$templateName}' for channel '{$channel}' not found");
        }

        return [
            'subject' => $this->replaceVariables($template->subject, $data),
            'body_html' => $this->replaceVariables($template->body_html, $data),
            'body_text' => $this->replaceVariables($template->body_text, $data),
            'body_sms' => $this->replaceVariables($template->body_sms, $data),
            'push_title' => $this->replaceVariables($template->push_title, $data),
            'push_body' => $this->replaceVariables($template->push_body, $data),
        ];
    }

    /**
     * Replace variables in template string
     * 
     * Supports: {{ variable }}, {{ object.property }}, {{ array.0 }}
     */
    protected function replaceVariables(?string $template, array $data): ?string
    {
        if (empty($template)) {
            return $template;
        }

        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}/', function ($matches) use ($data) {
            $key = $matches[1];

            // Support dot notation (e.g., user.name, order.items.0)
            $value = $this->getNestedValue($data, $key);

            return $value !== null ? $value : $matches[0];
        }, $template);
    }

    /**
     * Get nested value from array using dot notation
     */
    protected function getNestedValue(array $data, string $key)
    {
        $keys = explode('.', $key);
        $value = $data;

        foreach ($keys as $k) {
            if (is_array($value) && isset($value[$k])) {
                $value = $value[$k];
            } elseif (is_object($value) && isset($value->$k)) {
                $value = $value->$k;
            } else {
                return null;
            }
        }

        return $value;
    }

    /**
     * Validate template variables
     */
    public function validateTemplate(string $template, array $requiredVariables): bool
    {
        foreach ($requiredVariables as $var) {
            if (
                strpos($template, "{{ {$var} }}") === false &&
                strpos($template, "{{" . $var . "}}") === false
            ) {
                return false;
            }
        }

        return true;
    }
}
