<?php

namespace Modules\Settings\Controllers;

use App\Core\Application;
use Modules\Settings\Services\SettingsService;

class MailSettingsController
{
    protected SettingsService $settingsService;

    public function __construct()
    {
        $this->settingsService = new SettingsService();
    }

    public function index()
    {
        $app = Application::getInstance();
        $settings = $this->settingsService->getMailSettings();

        echo $app->view->render('settings/mail', [
            'title' => 'Configuration Mail',
            'settings' => $settings,
        ]);
    }

    public function update()
    {
        $app = Application::getInstance();

        $settings = [
            'mail_driver' => $_POST['mail_driver'] ?? 'smtp',
            'mail_host' => $_POST['mail_host'] ?? '',
            'mail_port' => $_POST['mail_port'] ?? '587',
            'mail_username' => $_POST['mail_username'] ?? '',
            'mail_password' => $_POST['mail_password'] ?? '',
            'mail_encryption' => $_POST['mail_encryption'] ?? 'tls',
            'mail_from_address' => $_POST['mail_from_address'] ?? '',
            'mail_from_name' => $_POST['mail_from_name'] ?? '',
        ];

        foreach ($settings as $key => $value) {
            $this->settingsService->set($key, $value, 'string', 'mail');
        }

        $_SESSION['flash_success'] = 'Configuration mail mise à jour avec succès.';
        redirect('/admin/settings');
    }

    public function sendTest()
    {
        $app = Application::getInstance();

        $to = $_POST['test_email'] ?? '';

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Adresse email invalide.';
            redirect('/admin/settings/mail');
        }

        // Test email sending
        $settings = $this->settingsService->getMailSettings();

        $result = $this->sendTestEmail($to, $settings);

        if ($result) {
            $_SESSION['flash_success'] = 'Email de test envoyé avec succès à ' . $to;
        } else {
            $_SESSION['flash_error'] = 'Échec de l\'envoi de l\'email de test.';
        }

        redirect('/admin/settings/mail');
    }

    protected function sendTestEmail(string $to, array $settings): bool
    {
        // Simple mail test using PHP mail() or SMTP
        $subject = 'Test Email from ' . $this->settingsService->get('site_name', 'SunuFramework');
        $message = 'Ceci est un email de test pour vérifier la configuration mail.';

        $headers = [
            'From: ' . $settings['mail_from_address'],
            'Reply-To: ' . $settings['mail_from_address'],
            'X-Mailer: PHP/' . phpversion(),
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8'
        ];

        return mail($to, $subject, $message, implode("\r\n", $headers));
    }
}
