<?php

namespace App\Core\Console\Command;

use App\Core\Application;
use App\Core\Encryption\Encrypter;

class KeyGenerateCommand
{
    public function execute(Application $app, array $args): void
    {
        $show = in_array('--show', $args);
        $force = in_array('--force', $args);

        // Check if key already exists
        $envPath = $app->getBasePath() . '/.env';

        if (!file_exists($envPath)) {
            echo "\033[31mError: .env file not found.\033[0m\n";
            echo "Please create a .env file first (you can copy .env.example).\n";
            return;
        }

        // Read current .env
        $envContent = file_get_contents($envPath);

        // Check if APP_KEY already exists and has a value
        if (preg_match('/^APP_KEY=(.+)$/m', $envContent, $matches)) {
            $existingKey = trim($matches[1]);
            if (!empty($existingKey) && !$force) {
                echo "\033[33mWarning: APP_KEY already exists in .env file.\033[0m\n";
                echo "Use --force to override the existing key.\n";
                echo "\nCurrent key: " . $existingKey . "\n";
                return;
            }
        }

        // Generate new key
        $key = $this->generateRandomKey();

        if ($show) {
            // Just show the key without updating .env
            echo "\033[32mGenerated key:\033[0m\n";
            echo "base64:" . $key . "\n";
            return;
        }

        // Update .env file
        if (preg_match('/^APP_KEY=.*$/m', $envContent)) {
            // Replace existing APP_KEY line
            $envContent = preg_replace(
                '/^APP_KEY=.*$/m',
                'APP_KEY=base64:' . $key,
                $envContent
            );
        } else {
            // Add APP_KEY if it doesn't exist
            $envContent .= "\nAPP_KEY=base64:" . $key . "\n";
        }

        // Write back to .env
        if (file_put_contents($envPath, $envContent)) {
            echo "\033[32m✓ Application key set successfully.\033[0m\n";
            echo "\nKey: base64:" . $key . "\n";

            // Update environment variable for current process
            putenv("APP_KEY=base64:" . $key);
        } else {
            echo "\033[31m✗ Error: Failed to write to .env file.\033[0m\n";
            echo "Please check file permissions.\n";
        }
    }

    /**
     * Generate a random key for the application.
     *
     * @return string
     */
    protected function generateRandomKey(): string
    {
        return base64_encode(Encrypter::generateKey('AES-256-CBC'));
    }
}
