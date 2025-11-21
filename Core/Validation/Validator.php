<?php

namespace App\Core\Validation;

/**
 * Validator
 * 
 * Système de validation de formulaires similaire à Laravel
 */
class Validator
{
    private array $data;
    private array $rules;
    private array $customMessages;
    private ErrorBag $errors;
    private array $validatedData = [];
    private bool $hasValidated = false;

    /**
     * Créer une nouvelle instance de validation
     */
    public function __construct(array $data, array $rules, array $customMessages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->customMessages = $customMessages;
        $this->errors = new ErrorBag();
    }

    /**
     * Créer une instance statique (façon Laravel)
     */
    public static function make(array $data, array $rules, array $customMessages = []): self
    {
        return new self($data, $rules, $customMessages);
    }

    /**
     * Valider les données
     */
    public function validate(): array
    {
        $this->performValidation();

        if ($this->fails()) {
            throw new ValidationException($this->errors, $this->validatedData);
        }

        return $this->validatedData;
    }

    /**
     * Effectuer la validation
     */
    private function performValidation(): void
    {
        // Éviter de valider plusieurs fois
        if ($this->hasValidated) {
            return;
        }

        $this->hasValidated = true;

        foreach ($this->rules as $field => $rulesString) {
            $value = $this->getValue($field);
            $rules = $this->parseRules($rulesString);

            foreach ($rules as $rule) {
                $this->validateRule($field, $value, $rule);
            }

            // Si pas d'erreur, ajouter aux données validées
            if (!$this->errors->has($field)) {
                $this->validatedData[$field] = $value;
            }
        }
    }

    /**
     * Obtenir la valeur d'un champ
     */
    private function getValue(string $field)
    {
        return $this->data[$field] ?? null;
    }

    /**
     * Parser les règles d'un champ
     */
    private function parseRules(string|array $rulesString): array
    {
        if (is_array($rulesString)) {
            return $rulesString;
        }

        return array_map('trim', explode('|', $rulesString));
    }

    /**
     * Valider une règle spécifique
     */
    private function validateRule(string $field, $value, string $rule): void
    {
        [$ruleName, $parameters] = $this->parseRule($rule);

        $passed = match ($ruleName) {
            'required' => $this->validateRequired($value),
            'email' => $this->validateEmail($value),
            'min' => $this->validateMin($value, $parameters[0] ?? 0),
            'max' => $this->validateMax($value, $parameters[0] ?? 0),
            'numeric' => $this->validateNumeric($value),
            'integer' => $this->validateInteger($value),
            'string' => $this->validateString($value),
            'confirmed' => $this->validateConfirmed($field, $value),
            'unique' => $this->validateUnique($value, $parameters),
            'exists' => $this->validateExists($value, $parameters),
            'in' => $this->validateIn($value, $parameters),
            'not_in' => $this->validateNotIn($value, $parameters),
            'regex' => $this->validateRegex($value, $parameters[0] ?? ''),
            'url' => $this->validateUrl($value),
            'date' => $this->validateDate($value),
            'before' => $this->validateBefore($value, $parameters[0] ?? ''),
            'after' => $this->validateAfter($value, $parameters[0] ?? ''),
            'alpha' => $this->validateAlpha($value),
            'alpha_num' => $this->validateAlphaNum($value),
            'alpha_dash' => $this->validateAlphaDash($value),
            'array' => $this->validateArray($value),
            'boolean' => $this->validateBoolean($value),
            'same' => $this->validateSame($field, $value, $parameters[0] ?? ''),
            'different' => $this->validateDifferent($field, $value, $parameters[0] ?? ''),
            default => true // Règle inconnue, on passe
        };

        if (!$passed) {
            $this->addErrorMessage($field, $ruleName, $parameters);
        }
    }

    /**
     * Parser une règle et ses paramètres
     */
    private function parseRule(string $rule): array
    {
        if (str_contains($rule, ':')) {
            [$name, $params] = explode(':', $rule, 2);
            return [$name, explode(',', $params)];
        }

        return [$rule, []];
    }

    /**
     * Ajouter une erreur (méthode privée interne)
     */
    private function addErrorMessage(string $field, string $rule, array $parameters = []): void
    {
        $message = $this->getMessage($field, $rule, $parameters);
        $this->errors->add($field, $message);
    }

    /**
     * Obtenir le message d'erreur
     */
    private function getMessage(string $field, string $rule, array $parameters = []): string
    {
        // Vérifier si un message personnalisé existe
        $customKey = "{$field}.{$rule}";
        if (isset($this->customMessages[$customKey])) {
            return $this->customMessages[$customKey];
        }

        if (isset($this->customMessages[$rule])) {
            return str_replace(':field', $this->formatFieldName($field), $this->customMessages[$rule]);
        }

        // Utiliser le message par défaut
        $replacements = ['field' => $this->formatFieldName($field)];

        // Ajouter les paramètres de remplacement
        if (!empty($parameters)) {
            $replacements['min'] = $parameters[0] ?? '';
            $replacements['max'] = $parameters[0] ?? '';
            $replacements['date'] = $parameters[0] ?? '';
            $replacements['other'] = $this->formatFieldName($parameters[0] ?? '');
        }

        return Rules::getMessage($rule, $replacements);
    }

    /**
     * Formater le nom du champ pour l'affichage
     */
    private function formatFieldName(string $field): string
    {
        return ucfirst(str_replace('_', ' ', $field));
    }

    // ==================== Méthodes de validation ====================

    private function validateRequired($value): bool
    {
        return Rules::required($value);
    }

    private function validateEmail($value): bool
    {
        return Rules::email($value);
    }

    private function validateMin($value, $min): bool
    {
        return Rules::min($value, (int)$min);
    }

    private function validateMax($value, $max): bool
    {
        return Rules::max($value, (int)$max);
    }

    private function validateNumeric($value): bool
    {
        return Rules::numeric($value);
    }

    private function validateInteger($value): bool
    {
        return Rules::integer($value);
    }

    private function validateString($value): bool
    {
        return Rules::string($value);
    }

    private function validateConfirmed(string $field, $value): bool
    {
        $confirmField = $field . '_confirmation';
        $confirmValue = $this->data[$confirmField] ?? null;
        return Rules::confirmed($value, $confirmValue);
    }

    private function validateUnique($value, array $parameters): bool
    {
        $table = $parameters[0] ?? '';
        $column = $parameters[1] ?? 'id';
        $ignoreId = $parameters[2] ?? null;

        return Rules::unique($table, $column, $value, $ignoreId);
    }

    private function validateExists($value, array $parameters): bool
    {
        $table = $parameters[0] ?? '';
        $column = $parameters[1] ?? 'id';

        return Rules::exists($table, $column, $value);
    }

    private function validateIn($value, array $list): bool
    {
        return Rules::in($value, $list);
    }

    private function validateNotIn($value, array $list): bool
    {
        return Rules::notIn($value, $list);
    }

    private function validateRegex($value, string $pattern): bool
    {
        return Rules::regex($value, $pattern);
    }

    private function validateUrl($value): bool
    {
        return Rules::url($value);
    }

    private function validateDate($value): bool
    {
        return Rules::date($value);
    }

    private function validateBefore($value, string $date): bool
    {
        return Rules::before($value, $date);
    }

    private function validateAfter($value, string $date): bool
    {
        return Rules::after($value, $date);
    }

    private function validateAlpha($value): bool
    {
        return Rules::alpha($value);
    }

    private function validateAlphaNum($value): bool
    {
        return Rules::alphaNum($value);
    }

    private function validateAlphaDash($value): bool
    {
        return Rules::alphaDash($value);
    }

    private function validateArray($value): bool
    {
        return Rules::isArray($value);
    }

    private function validateBoolean($value): bool
    {
        return Rules::boolean($value);
    }

    private function validateSame(string $field, $value, string $otherField): bool
    {
        $otherValue = $this->data[$otherField] ?? null;
        return Rules::same($value, $otherValue);
    }

    private function validateDifferent(string $field, $value, string $otherField): bool
    {
        $otherValue = $this->data[$otherField] ?? null;
        return Rules::different($value, $otherValue);
    }

    // ==================== Méthodes publiques ====================

    /**
     * Vérifier si la validation a échoué
     */
    public function fails(): bool
    {
        $this->performValidation();
        return $this->errors->any();
    }

    /**
     * Vérifier si la validation a réussi
     */
    public function passes(): bool
    {
        return !$this->fails();
    }

    /**
     * Obtenir les erreurs
     */
    public function errors(): ErrorBag
    {
        return $this->errors;
    }

    /**
     * Obtenir les données validées
     */
    public function validated(): array
    {
        $this->performValidation();
        return $this->validatedData;
    }

    /**
     * Obtenir toutes les données (même invalides)
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Ajouter une erreur manuellement
     */
    public function addError(string $field, string $message): void
    {
        $this->errors->add($field, $message);
    }
}
