<?php

namespace Modules\Settings\Database\Seeders;

use App\Core\Database\Database;

class DefaultSettingsSeeder
{
    public function run(): void
    {
        $db = Database::getInstance();
        $settings = [
            // Site Settings
            ['key' => 'site_name', 'value' => 'SunuFramework', 'type' => 'string', 'group' => 'site', 'description' => 'Nom du site', 'is_public' => true],
            ['key' => 'site_description', 'value' => 'Modern PHP Framework for rapid development', 'type' => 'string', 'group' => 'site', 'description' => 'Description du site', 'is_public' => true],
            ['key' => 'site_logo', 'value' => '/assets/images/logo/logo.png', 'type' => 'string', 'group' => 'site', 'description' => 'Logo du site', 'is_public' => true],
            ['key' => 'site_favicon', 'value' => '/assets/images/logo/favicon.png', 'type' => 'string', 'group' => 'site', 'description' => 'Favicon du site', 'is_public' => true],
            ['key' => 'default_language', 'value' => 'fr', 'type' => 'string', 'group' => 'site', 'description' => 'Langue par défaut', 'is_public' => true],
            ['key' => 'default_timezone', 'value' => 'Africa/Dakar', 'type' => 'string', 'group' => 'site', 'description' => 'Fuseau horaire par défaut', 'is_public' => true],
            ['key' => 'date_format', 'value' => 'Y-m-d', 'type' => 'string', 'group' => 'site', 'description' => 'Format de date', 'is_public' => true],
            ['key' => 'time_format', 'value' => 'H:i', 'type' => 'string', 'group' => 'site', 'description' => 'Format d\'heure', 'is_public' => true],
            ['key' => 'items_per_page', 'value' => '20', 'type' => 'integer', 'group' => 'site', 'description' => 'Nombre d\'éléments par page', 'is_public' => true],
            ['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean', 'group' => 'site', 'description' => 'Mode maintenance', 'is_public' => false],
            ['key' => 'registration_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'site', 'description' => 'Inscription activée', 'is_public' => true],

            // Theme Settings
            ['key' => 'theme_mode', 'value' => 'light', 'type' => 'string', 'group' => 'theme', 'description' => 'Mode du thème', 'is_public' => true],
            ['key' => 'primary_color', 'value' => '#7366FF', 'type' => 'string', 'group' => 'theme', 'description' => 'Couleur primaire', 'is_public' => true],
            ['key' => 'secondary_color', 'value' => '#838383', 'type' => 'string', 'group' => 'theme', 'description' => 'Couleur secondaire', 'is_public' => true],
            ['key' => 'success_color', 'value' => '#65c15c', 'type' => 'string', 'group' => 'theme', 'description' => 'Couleur de succès', 'is_public' => true],
            ['key' => 'sidebar_type', 'value' => 'compact-sidebar', 'type' => 'string', 'group' => 'theme', 'description' => 'Type de sidebar', 'is_public' => true],
            ['key' => 'sidebar_icon', 'value' => 'stroke-svg', 'type' => 'string', 'group' => 'theme', 'description' => 'Type d\'icône sidebar', 'is_public' => true],
            ['key' => 'layout_type', 'value' => 'ltr', 'type' => 'string', 'group' => 'theme', 'description' => 'Direction du layout', 'is_public' => true],
            ['key' => 'font_family', 'value' => 'Rubik', 'type' => 'string', 'group' => 'theme', 'description' => 'Famille de police', 'is_public' => true],
            ['key' => 'font_size', 'value' => '14px', 'type' => 'string', 'group' => 'theme', 'description' => 'Taille de police', 'is_public' => true],

            // API Settings
            ['key' => 'openai_api_key', 'value' => '', 'type' => 'string', 'group' => 'api', 'description' => 'Clé API OpenAI', 'is_public' => false],
            ['key' => 'google_api_key', 'value' => '', 'type' => 'string', 'group' => 'api', 'description' => 'Clé API Google', 'is_public' => false],
            ['key' => 'stripe_api_key', 'value' => '', 'type' => 'string', 'group' => 'api', 'description' => 'Clé API Stripe', 'is_public' => false],
            ['key' => 'paypal_client_id', 'value' => '', 'type' => 'string', 'group' => 'api', 'description' => 'ID Client PayPal', 'is_public' => false],
            ['key' => 'sms_api_key', 'value' => '', 'type' => 'string', 'group' => 'api', 'description' => 'Clé API SMS', 'is_public' => false],
            ['key' => 'map_api_key', 'value' => '', 'type' => 'string', 'group' => 'api', 'description' => 'Clé API Maps', 'is_public' => false],

            // Mail Settings
            ['key' => 'mail_driver', 'value' => 'smtp', 'type' => 'string', 'group' => 'mail', 'description' => 'Driver de mail', 'is_public' => false],
            ['key' => 'mail_host', 'value' => 'smtp.mailtrap.io', 'type' => 'string', 'group' => 'mail', 'description' => 'Hôte SMTP', 'is_public' => false],
            ['key' => 'mail_port', 'value' => '587', 'type' => 'string', 'group' => 'mail', 'description' => 'Port SMTP', 'is_public' => false],
            ['key' => 'mail_username', 'value' => '', 'type' => 'string', 'group' => 'mail', 'description' => 'Nom d\'utilisateur SMTP', 'is_public' => false],
            ['key' => 'mail_password', 'value' => '', 'type' => 'string', 'group' => 'mail', 'description' => 'Mot de passe SMTP', 'is_public' => false],
            ['key' => 'mail_encryption', 'value' => 'tls', 'type' => 'string', 'group' => 'mail', 'description' => 'Chiffrement SMTP', 'is_public' => false],
            ['key' => 'mail_from_address', 'value' => 'noreply@sunuframework.local', 'type' => 'string', 'group' => 'mail', 'description' => 'Adresse d\'expéditeur', 'is_public' => false],
            ['key' => 'mail_from_name', 'value' => 'SunuFramework', 'type' => 'string', 'group' => 'mail', 'description' => 'Nom d\'expéditeur', 'is_public' => false],
        ];

        foreach ($settings as $setting) {
            $isPublic = $setting['is_public'] ? 1 : 0;
            $sql = "INSERT INTO settings (`key`, value, type, setting_group, description, is_public, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = NOW()";

            $stmt = $db->getPdo()->prepare($sql);
            $stmt->execute([
                $setting['key'],
                $setting['value'],
                $setting['type'],
                $setting['group'],
                $setting['description'],
                $isPublic
            ]);
        }
    }
}
