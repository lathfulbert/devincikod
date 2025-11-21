<?php

namespace App\Core\Validation;

use Exception;

/**
 * ValidationException
 * 
 * Exception levée quand la validation échoue
 */
class ValidationException extends Exception
{
    protected ErrorBag $errors;
    protected array $validatedData;

    public function __construct(ErrorBag $errors, array $validatedData = [])
    {
        $this->errors = $errors;
        $this->validatedData = $validatedData;

        $message = "La validation a échoué avec " . $errors->count() . " erreur(s).";
        parent::__construct($message);
    }

    /**
     * Obtenir les erreurs de validation
     */
    public function errors(): ErrorBag
    {
        return $this->errors;
    }

    /**
     * Obtenir les données validées (même partielles)
     */
    public function validated(): array
    {
        return $this->validatedData;
    }

    /**
     * Obtenir les erreurs sous forme de tableau
     */
    public function getErrors(): array
    {
        return $this->errors->toArray();
    }
}
