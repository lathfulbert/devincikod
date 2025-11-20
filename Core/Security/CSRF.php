<?php

namespace App\Core\Security;

class CSRF
{
    private static ?CSRF $instance = null;
    private const TOKEN_NAME = '_csrf_token';
    private const TOKEN_LENGTH = 32;

    private function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function getInstance(): CSRF
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Génère un nouveau token CSRF et le stocke en session
     */
    public function generateToken(): string
    {
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $_SESSION[self::TOKEN_NAME] = $token;
        return $token;
    }

    /**
     * Récupère le token CSRF actuel (ou en génère un nouveau)
     */
    public function getToken(): string
    {
        if (!isset($_SESSION[self::TOKEN_NAME])) {
            return $this->generateToken();
        }
        return $_SESSION[self::TOKEN_NAME];
    }

    /**
     * Valide un token CSRF fourni
     */
    public function validateToken(?string $token): bool
    {
        if ($token === null) {
            return false;
        }

        if (!isset($_SESSION[self::TOKEN_NAME])) {
            return false;
        }

        return hash_equals($_SESSION[self::TOKEN_NAME], $token);
    }

    /**
     * Génère un champ input hidden HTML avec le token CSRF
     */
    public function getTokenField(): string
    {
        $token = $this->getToken();
        return '<input type="hidden" name="' . self::TOKEN_NAME . '" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Récupère le nom du champ token
     */
    public static function getTokenName(): string
    {
        return self::TOKEN_NAME;
    }

    /**
     * Régénère le token (utile après login/logout)
     */
    public function regenerateToken(): string
    {
        return $this->generateToken();
    }
}
