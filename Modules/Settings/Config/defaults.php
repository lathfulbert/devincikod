<?php

/**
 * Configuration par défaut du module Settings
 *
 * Ce fichier contient toutes les valeurs par défaut pour les paramètres du module.
 * Ces valeurs sont utilisées lors de l'installation initiale et pour la réinitialisation.
 */

return [
    /**
     * Paramètres du Site
     */
    'site' => [
        'site_name' => 'SunuFramework',
        'site_description' => 'Modern PHP Framework for rapid development',
        'site_logo' => '/assets/images/logo/logo.png',
        'site_favicon' => '/assets/images/logo/favicon.png',
        'default_language' => 'fr',
        'default_timezone' => 'Africa/Dakar',
        'date_format' => 'Y-m-d',
        'time_format' => 'H:i',
        'items_per_page' => 20,
        'maintenance_mode' => false,
        'registration_enabled' => true,
    ],

    /**
     * Paramètres du Thème
     */
    'theme' => [
        'theme_mode' => 'light', // light, dark, auto
        'primary_color' => '#7366FF',
        'secondary_color' => '#838383',
        'success_color' => '#65c15c',
        'warning_color' => '#f0ad4e',
        'danger_color' => '#d9534f',
        'info_color' => '#5bc0de',
        'sidebar_type' => 'compact-sidebar', // compact-sidebar, material-layout, dark-sidebar
        'sidebar_icon' => 'stroke-svg', // stroke-svg, fill-svg
        'layout_type' => 'ltr', // ltr, rtl
        'font_family' => 'Rubik',
        'font_size' => '14px',
    ],

    /**
     * Clés API
     */
    'api' => [
        'openai_api_key' => '',
        'google_api_key' => '',
        'stripe_api_key' => '',
        'stripe_secret_key' => '',
        'paypal_client_id' => '',
        'paypal_secret' => '',
        'sms_api_key' => '',
        'map_api_key' => '',
        'recaptcha_site_key' => '',
        'recaptcha_secret_key' => '',
    ],

    /**
     * Configuration Mail (SMTP)
     */
    'mail' => [
        'mail_driver' => 'smtp',
        'mail_host' => 'smtp.mailtrap.io',
        'mail_port' => '587',
        'mail_username' => '',
        'mail_password' => '',
        'mail_encryption' => 'tls', // tls, ssl
        'mail_from_address' => 'noreply@sunuframework.local',
        'mail_from_name' => 'SunuFramework',
    ],

    /**
     * Langues Supportées
     */
    'languages' => [
        'fr' => 'Français',
        'en' => 'English',
        'ar' => 'العربية',
        'es' => 'Español',
        'de' => 'Deutsch',
        'pt' => 'Português',
        'it' => 'Italiano',
        'zh' => '中文',
    ],

    /**
     * Fuseaux Horaires Recommandés
     */
    'timezones' => [
        'Africa/Dakar' => 'Dakar',
        'Africa/Casablanca' => 'Casablanca',
        'Africa/Cairo' => 'Cairo',
        'Europe/Paris' => 'Paris',
        'Europe/London' => 'London',
        'America/New_York' => 'New York',
        'America/Los_Angeles' => 'Los Angeles',
        'Asia/Tokyo' => 'Tokyo',
        'Asia/Dubai' => 'Dubai',
    ],

    /**
     * Événements de Webhook Disponibles
     */
    'webhook_events' => [
        'user.created' => 'Utilisateur créé',
        'user.updated' => 'Utilisateur modifié',
        'user.deleted' => 'Utilisateur supprimé',
        'user.login' => 'Connexion utilisateur',
        'user.logout' => 'Déconnexion utilisateur',
        'order.created' => 'Commande créée',
        'order.updated' => 'Commande modifiée',
        'order.completed' => 'Commande complétée',
        'order.cancelled' => 'Commande annulée',
        'payment.success' => 'Paiement réussi',
        'payment.failed' => 'Paiement échoué',
        'subscription.created' => 'Abonnement créé',
        'subscription.cancelled' => 'Abonnement annulé',
    ],

    /**
     * Types de Sidebar Disponibles
     */
    'sidebar_types' => [
        'compact-sidebar' => 'Compact',
        'material-layout' => 'Material',
        'dark-sidebar' => 'Dark',
        'compact-wrap' => 'Compact Wrap',
        'compact-small' => 'Compact Small',
        'enterprice-type' => 'Enterprise',
        'modern-layout' => 'Modern',
        'color-sidebar' => 'Colored',
    ],

    /**
     * Formats de Date Disponibles
     */
    'date_formats' => [
        'Y-m-d' => '2025-01-23',
        'd/m/Y' => '23/01/2025',
        'm/d/Y' => '01/23/2025',
        'd-m-Y' => '23-01-2025',
        'F j, Y' => 'January 23, 2025',
        'j F Y' => '23 January 2025',
    ],

    /**
     * Formats d'Heure Disponibles
     */
    'time_formats' => [
        'H:i' => '14:30',
        'H:i:s' => '14:30:45',
        'h:i A' => '02:30 PM',
        'h:i:s A' => '02:30:45 PM',
    ],

    /**
     * Drivers Mail Disponibles
     */
    'mail_drivers' => [
        'smtp' => 'SMTP',
        'sendmail' => 'Sendmail',
        'mailgun' => 'Mailgun',
        'ses' => 'Amazon SES',
        'postmark' => 'Postmark',
        'log' => 'Log (Development)',
    ],
];
