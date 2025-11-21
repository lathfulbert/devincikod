<?php

namespace App\Core\Validation;

/**
 * ErrorBag
 * 
 * Gestion de la collection d'erreurs de validation
 */
class ErrorBag
{
    private array $errors = [];

    /**
     * Ajouter une erreur pour un champ
     */
    public function add(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * Vérifier si un champ a des erreurs
     */
    public function has(string $field): bool
    {
        return isset($this->errors[$field]) && !empty($this->errors[$field]);
    }

    /**
     * Obtenir toutes les erreurs d'un champ
     */
    public function get(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    /**
     * Obtenir la première erreur d'un champ
     */
    public function first(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Obtenir toutes les erreurs
     */
    public function all(): array
    {
        return $this->errors;
    }

    /**
     * Vérifier si il y a des erreurs
     */
    public function any(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Vérifier si le bag est vide
     */
    public function isEmpty(): bool
    {
        return empty($this->errors);
    }

    /**
     * Compter le nombre total d'erreurs
     */
    public function count(): int
    {
        return array_sum(array_map('count', $this->errors));
    }

    /**
     * Obtenir toutes les erreurs sous forme de tableau plat
     */
    public function flatten(): array
    {
        $flattened = [];
        foreach ($this->errors as $field => $messages) {
            foreach ($messages as $message) {
                $flattened[] = $message;
            }
        }
        return $flattened;
    }

    /**
     * Convertir en JSON pour les APIs
     */
    public function toJson(): string
    {
        return json_encode($this->errors);
    }

    /**
     * Convertir en tableau
     */
    public function toArray(): array
    {
        return $this->errors;
    }

    /**
     * Vider toutes les erreurs
     */
    public function clear(): void
    {
        $this->errors = [];
    }
}
