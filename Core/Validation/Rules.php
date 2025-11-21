<?php

namespace App\Core\Validation;

use App\Core\Database\Database;

/**
 * Rules
 * 
 * Règles de validation pour le système de validation
 */
class Rules
{
    /**
     * Messages d'erreur par défaut en français
     */
    private static array $messages = [
        'required' => 'Le champ :field est obligatoire.',
        'email' => 'Le champ :field doit être une adresse email valide.',
        'min' => 'Le champ :field doit contenir au moins :min caractères.',
        'max' => 'Le champ :field ne doit pas dépasser :max caractères.',
        'numeric' => 'Le champ :field doit être un nombre.',
        'integer' => 'Le champ :field doit être un entier.',
        'string' => 'Le champ :field doit être une chaîne de caractères.',
        'confirmed' => 'La confirmation du champ :field ne correspond pas.',
        'unique' => 'La valeur du champ :field existe déjà.',
        'exists' => 'La valeur sélectionnée pour :field est invalide.',
        'in' => 'La valeur du champ :field n\'est pas valide.',
        'not_in' => 'La valeur du champ :field n\'est pas autorisée.',
        'regex' => 'Le format du champ :field est invalide.',
        'url' => 'Le champ :field doit être une URL valide.',
        'date' => 'Le champ :field doit être une date valide.',
        'before' => 'Le champ :field doit être une date antérieure à :date.',
        'after' => 'Le champ :field doit être une date postérieure à :date.',
        'alpha' => 'Le champ :field ne doit contenir que des lettres.',
        'alpha_num' => 'Le champ :field ne doit contenir que des lettres et des chiffres.',
        'alpha_dash' => 'Le champ :field ne doit contenir que des lettres, chiffres, tirets et underscores.',
        'array' => 'Le champ :field doit être un tableau.',
        'boolean' => 'Le champ :field doit être vrai ou faux.',
        'same' => 'Le champ :field doit correspondre à :other.',
        'different' => 'Le champ :field doit être différent de :other.',
    ];

    /**
     * Obtenir le message d'erreur pour une règle
     */
    public static function getMessage(string $rule, array $replacements = []): string
    {
        $message = self::$messages[$rule] ?? "La validation du champ :field a échoué.";

        foreach ($replacements as $key => $value) {
            $message = str_replace(':' . $key, $value, $message);
        }

        return $message;
    }

    /**
     * Valider que le champ est présent et non vide
     */
    public static function required($value): bool
    {
        if (is_null($value)) {
            return false;
        }

        if (is_string($value) && trim($value) === '') {
            return false;
        }

        if (is_array($value) && empty($value)) {
            return false;
        }

        return true;
    }

    /**
     * Valider le format email
     */
    public static function email($value): bool
    {
        if (empty($value)) {
            return true; // Skip if empty (use required for mandatory)
        }
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valider la longueur minimale
     */
    public static function min($value, int $min): bool
    {
        if (empty($value)) {
            return true;
        }

        if (is_string($value)) {
            return mb_strlen($value) >= $min;
        }

        if (is_numeric($value)) {
            return $value >= $min;
        }

        if (is_array($value)) {
            return count($value) >= $min;
        }

        return false;
    }

    /**
     * Valider la longueur maximale
     */
    public static function max($value, int $max): bool
    {
        if (empty($value)) {
            return true;
        }

        if (is_string($value)) {
            return mb_strlen($value) <= $max;
        }

        if (is_numeric($value)) {
            return $value <= $max;
        }

        if (is_array($value)) {
            return count($value) <= $max;
        }

        return false;
    }

    /**
     * Valider que c'est numérique
     */
    public static function numeric($value): bool
    {
        if (empty($value)) {
            return true;
        }
        return is_numeric($value);
    }

    /**
     * Valider que c'est un entier
     */
    public static function integer($value): bool
    {
        if (empty($value)) {
            return true;
        }
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Valider que c'est une chaîne
     */
    public static function string($value): bool
    {
        if (empty($value)) {
            return true;
        }
        return is_string($value);
    }

    /**
     * Valider confirmation de champ (ex: password et password_confirmation)
     */
    public static function confirmed($value, $confirmValue): bool
    {
        return $value === $confirmValue;
    }

    /**
     * Valider l'unicité en base de données
     */
    public static function unique(string $table, string $column, $value, $ignoreId = null): bool
    {
        if (empty($value)) {
            return true;
        }

        try {
            $db = Database::getInstance();
            $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
            $params = [$value];

            if ($ignoreId) {
                $sql .= " AND id != ?";
                $params[] = $ignoreId;
            }

            $stmt = $db->query($sql, $params);
            $result = $stmt->fetch();

            return $result['count'] == 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Valider l'existence en base de données
     */
    public static function exists(string $table, string $column, $value): bool
    {
        if (empty($value)) {
            return true;
        }

        try {
            $db = Database::getInstance();
            $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
            $stmt = $db->query($sql, [$value]);
            $result = $stmt->fetch();

            return $result['count'] > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Valider que la valeur est dans une liste
     */
    public static function in($value, array $list): bool
    {
        if (empty($value)) {
            return true;
        }
        return in_array($value, $list, true);
    }

    /**
     * Valider que la valeur n'est pas dans une liste
     */
    public static function notIn($value, array $list): bool
    {
        if (empty($value)) {
            return true;
        }
        return !in_array($value, $list, true);
    }

    /**
     * Valider avec une expression régulière
     */
    public static function regex($value, string $pattern): bool
    {
        if (empty($value)) {
            return true;
        }
        return preg_match($pattern, $value) === 1;
    }

    /**
     * Valider une URL
     */
    public static function url($value): bool
    {
        if (empty($value)) {
            return true;
        }
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Valider une date
     */
    public static function date($value): bool
    {
        if (empty($value)) {
            return true;
        }
        return strtotime($value) !== false;
    }

    /**
     * Valider une date avant une autre
     */
    public static function before($value, string $date): bool
    {
        if (empty($value)) {
            return true;
        }
        return strtotime($value) < strtotime($date);
    }

    /**
     * Valider une date après une autre
     */
    public static function after($value, string $date): bool
    {
        if (empty($value)) {
            return true;
        }
        return strtotime($value) > strtotime($date);
    }

    /**
     * Valider caractères alphabétiques uniquement
     */
    public static function alpha($value): bool
    {
        if (empty($value)) {
            return true;
        }
        return preg_match('/^[\pL\pM]+$/u', $value) === 1;
    }

    /**
     * Valider caractères alphanumériques uniquement
     */
    public static function alphaNum($value): bool
    {
        if (empty($value)) {
            return true;
        }
        return preg_match('/^[\pL\pM\pN]+$/u', $value) === 1;
    }

    /**
     * Valider alpha + tirets et underscores
     */
    public static function alphaDash($value): bool
    {
        if (empty($value)) {
            return true;
        }
        return preg_match('/^[\pL\pM\pN_-]+$/u', $value) === 1;
    }

    /**
     * Valider que c'est un tableau
     */
    public static function isArray($value): bool
    {
        return is_array($value);
    }

    /**
     * Valider que c'est un booléen
     */
    public static function boolean($value): bool
    {
        return in_array($value, [true, false, 0, 1, '0', '1'], true);
    }

    /**
     * Valider que deux champs sont identiques
     */
    public static function same($value, $otherValue): bool
    {
        return $value === $otherValue;
    }

    /**
     * Valider que deux champs sont différents
     */
    public static function different($value, $otherValue): bool
    {
        return $value !== $otherValue;
    }
}
