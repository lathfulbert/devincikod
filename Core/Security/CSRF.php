<?php

namespace App\Core\Security;

class CSRF
{
    private static ?CSRF $instance = null;
    private const TOKEN_NAME = '_csrf_token';
    private const TOKEN_LENGTH = 32;
    private const SIGNED_TOKEN_NAME = '_csrf_signed';

    private function __construct()
    {
        if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_NONE) {
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
     * Get the APP_KEY for signing
     */
    private function getAppKey(): string
    {
        $key = env('APP_KEY');
        if (empty($key)) {
            throw new \RuntimeException('APP_KEY is required for CSRF token signing');
        }
        return $key;
    }

    /**
     * Sign a token with APP_KEY using HMAC
     */
    private function signToken(string $token): string
    {
        return hash_hmac('sha256', $token, $this->getAppKey());
    }

    /**
     * Verify token signature
     */
    private function verifySignature(string $token, string $signature): bool
    {
        $expected = $this->signToken($token);
        return hash_equals($expected, $signature);
    }

    /**
     * Génère un nouveau token CSRF et le stocke en session avec signature
     */
    public function generateToken(): string
    {
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $signature = $this->signToken($token);

        $_SESSION[self::TOKEN_NAME] = $token;
        $_SESSION[self::SIGNED_TOKEN_NAME] = $signature;

        return $token;
    }

    /**
     * Récupère le token CSRF actuel (ou en génère un nouveau)
     */
    public function getToken(): string
    {
        if (!isset($_SESSION[self::TOKEN_NAME]) || !isset($_SESSION[self::SIGNED_TOKEN_NAME])) {
            return $this->generateToken();
        }

        // Verify token signature to ensure it hasn't been tampered with
        $token = $_SESSION[self::TOKEN_NAME];
        $signature = $_SESSION[self::SIGNED_TOKEN_NAME];

        if (!$this->verifySignature($token, $signature)) {
            // Token has been tampered with, regenerate
            return $this->generateToken();
        }

        return $token;
    }

    /**
     * Valide un token CSRF fourni
     */
    public function validateToken(?string $token): bool
    {
        if ($token === null) {
            return false;
        }

        if (!isset($_SESSION[self::TOKEN_NAME]) || !isset($_SESSION[self::SIGNED_TOKEN_NAME])) {
            return false;
        }

        $storedToken = $_SESSION[self::TOKEN_NAME];
        $storedSignature = $_SESSION[self::SIGNED_TOKEN_NAME];

        // Verify signature first
        if (!$this->verifySignature($storedToken, $storedSignature)) {
            return false;
        }

        // Then verify the token itself
        return hash_equals($storedToken, $token);
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
