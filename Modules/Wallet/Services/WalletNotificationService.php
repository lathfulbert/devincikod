<?php

namespace Modules\Wallet\Services;

use Modules\Wallet\Models\WalletTopupRequest;
use Modules\Settings\Services\SettingsService;

/**
 * Wallet Notification Service
 *
 * Gère l'envoi de notifications pour les demandes de rechargement wallet
 */
class WalletNotificationService
{
    private SettingsService $settingsService;

    public function __construct()
    {
        $this->settingsService = new SettingsService();
    }

    /**
     * Envoyer notification de soumission de demande (confirmation utilisateur)
     */
    public function sendRequestSubmittedNotification(WalletTopupRequest $request): void
    {
        try {
            $user = $request->user;
            if (!$user || !$user->email) {
                return;
            }

            $data = [
                'user_name' => $user->name,
                'request_id' => $request->id,
                'amount' => number_format($request->amount, 0, ',', ' '),
                'payment_method' => $request->getPaymentMethodLabel(),
                'created_at' => date('d/m/Y à H:i', strtotime($request->created_at)),
                'view_url' => url('/admin/wallet/requests'),
                'site_name' => $this->settingsService->get('site_name', 'SunuFramework'),
                'year' => date('Y')
            ];

            $this->sendEmail(
                $user->email,
                'Demande de rechargement wallet soumise',
                'wallet_topup_request_submitted',
                $data
            );
        } catch (\Exception $e) {
            error_log("Failed to send request submitted notification: " . $e->getMessage());
        }
    }

    /**
     * Envoyer notification d'approbation (utilisateur)
     */
    public function sendRequestApprovedNotification(WalletTopupRequest $request, float $newBalance): void
    {
        try {
            $user = $request->user;
            if (!$user || !$user->email) {
                return;
            }

            $data = [
                'user_name' => $user->name,
                'request_id' => $request->id,
                'amount' => number_format($request->amount, 0, ',', ' '),
                'new_balance' => number_format($newBalance, 0, ',', ' '),
                'payment_method' => $request->getPaymentMethodLabel(),
                'created_at' => date('d/m/Y à H:i', strtotime($request->created_at)),
                'approved_at' => date('d/m/Y à H:i', strtotime($request->reviewed_at)),
                'reviewed_by' => $request->reviewer->name ?? 'Administrateur',
                'admin_notes' => $request->admin_notes ?? '',
                'wallet_url' => url('/admin/wallet'),
                'site_name' => $this->settingsService->get('site_name', 'SunuFramework'),
                'year' => date('Y')
            ];

            $this->sendEmail(
                $user->email,
                'Demande de rechargement approuvée - Wallet crédité',
                'wallet_topup_request_approved',
                $data
            );
        } catch (\Exception $e) {
            error_log("Failed to send request approved notification: " . $e->getMessage());
        }
    }

    /**
     * Envoyer notification de rejet (utilisateur)
     */
    public function sendRequestRejectedNotification(WalletTopupRequest $request): void
    {
        try {
            $user = $request->user;
            if (!$user || !$user->email) {
                return;
            }

            $data = [
                'user_name' => $user->name,
                'request_id' => $request->id,
                'amount' => number_format($request->amount, 0, ',', ' '),
                'payment_method' => $request->getPaymentMethodLabel(),
                'created_at' => date('d/m/Y à H:i', strtotime($request->created_at)),
                'rejected_at' => date('d/m/Y à H:i', strtotime($request->reviewed_at)),
                'reviewed_by' => $request->reviewer->name ?? 'Administrateur',
                'admin_notes' => $request->admin_notes ?? 'Aucune raison spécifiée',
                'support_url' => url('/admin/support'),
                'new_request_url' => url('/admin/wallet/topup'),
                'site_name' => $this->settingsService->get('site_name', 'SunuFramework'),
                'year' => date('Y')
            ];

            $this->sendEmail(
                $user->email,
                'Demande de rechargement rejetée',
                'wallet_topup_request_rejected',
                $data
            );
        } catch (\Exception $e) {
            error_log("Failed to send request rejected notification: " . $e->getMessage());
        }
    }

    /**
     * Envoyer notification aux admins (nouvelle demande en attente)
     */
    public function sendAdminNotification(WalletTopupRequest $request): void
    {
        try {
            $user = $request->user;
            if (!$user) {
                return;
            }

            // Récupérer les emails des admins
            $adminEmails = $this->getAdminEmails();
            if (empty($adminEmails)) {
                return;
            }

            $data = [
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_id' => $user->id,
                'request_id' => $request->id,
                'amount' => number_format($request->amount, 0, ',', ' '),
                'payment_method' => $request->getPaymentMethodLabel(),
                'created_at' => date('d/m/Y à H:i', strtotime($request->created_at)),
                'ip_address' => $request->ip_address ?? 'N/A',
                'user_notes' => $request->notes ?? '',
                'admin_review_url' => url('/admin/wallet/admin-requests?status=pending'),
                'site_name' => $this->settingsService->get('site_name', 'SunuFramework'),
                'year' => date('Y')
            ];

            foreach ($adminEmails as $adminEmail) {
                $this->sendEmail(
                    $adminEmail,
                    '[ADMIN] Nouvelle demande de rechargement en attente',
                    'wallet_topup_request_admin_notification',
                    $data
                );
            }
        } catch (\Exception $e) {
            error_log("Failed to send admin notification: " . $e->getMessage());
        }
    }

    /**
     * Envoyer email (méthode simplifiée)
     */
    private function sendEmail(string $to, string $subject, string $templateName, array $data): void
    {
        // Méthode 1 : Utiliser le système de notification existant (recommandé)
        // if (class_exists('\Modules\Notifications\Services\NotificationService')) {
        //     $notificationService = new \Modules\Notifications\Services\NotificationService(app());
        //     $notificationService->sendEmailNow($userId, $templateName, $data);
        //     return;
        // }

        // Méthode 2 : Envoi direct via PHPMailer ou service SMTP
        $this->sendEmailDirect($to, $subject, $templateName, $data);
    }

    /**
     * Envoi direct d'email (fallback)
     */
    private function sendEmailDirect(string $to, string $subject, string $templateName, array $data): void
    {
        try {
            // Charger le template depuis la base de données
            $template = \Modules\Notifications\Models\NotificationTemplate::findActive($templateName, 'email');

            if (!$template) {
                error_log("Template not found: {$templateName}");
                return;
            }

            // Remplacer les variables dans le template
            $body = $this->replaceVariables($template->body_html ?? $template->body_text, $data);
            $textBody = $this->replaceVariables($template->body_text, $data);

            // Configuration email depuis settings
            $mailConfig = [
                'smtp_host' => $this->settingsService->get('mail_host', 'localhost'),
                'smtp_port' => $this->settingsService->get('mail_port', 587),
                'smtp_username' => $this->settingsService->get('mail_username', ''),
                'smtp_password' => $this->settingsService->get('mail_password', ''),
                'smtp_encryption' => $this->settingsService->get('mail_encryption', 'tls'),
                'from_address' => $this->settingsService->get('mail_from_address', 'noreply@example.com'),
                'from_name' => $this->settingsService->get('mail_from_name', 'SunuFramework'),
            ];

            // Envoi via mail() PHP (simple) ou PHPMailer (recommandé)
            $headers = [
                'From: ' . $mailConfig['from_name'] . ' <' . $mailConfig['from_address'] . '>',
                'Reply-To: ' . $mailConfig['from_address'],
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8'
            ];

            $success = mail($to, $subject, $body, implode("\r\n", $headers));

            if (!$success) {
                error_log("Failed to send email to {$to}");
            }
        } catch (\Exception $e) {
            error_log("Email send error: " . $e->getMessage());
        }
    }

    /**
     * Remplacer les variables dans le template
     */
    private function replaceVariables(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            // Remplacer {{variable}}
            $template = str_replace('{{' . $key . '}}', $value, $template);

            // Gérer les conditionnels {{#variable}}...{{/variable}}
            if (!empty($value)) {
                $template = preg_replace('/\{\{#' . $key . '\}\}(.*?)\{\{\/' . $key . '\}\}/s', '$1', $template);
            } else {
                $template = preg_replace('/\{\{#' . $key . '\}\}(.*?)\{\{\/' . $key . '\}\}/s', '', $template);
            }
        }

        return $template;
    }

    /**
     * Récupérer les emails des administrateurs
     */
    private function getAdminEmails(): array
    {
        try {
            // Méthode 1 : Récupérer depuis la table users avec role admin
            $admins = \Modules\Users\Models\User::where('role', 'admin')
                ->orWhere('role', 'finance')
                ->get();

            $emails = [];
            foreach ($admins as $admin) {
                if ($admin->email) {
                    $emails[] = $admin->email;
                }
            }

            // Méthode 2 : Fallback depuis settings
            if (empty($emails)) {
                $adminEmail = $this->settingsService->get('admin_email');
                if ($adminEmail) {
                    $emails[] = $adminEmail;
                }
            }

            return $emails;
        } catch (\Exception $e) {
            error_log("Failed to get admin emails: " . $e->getMessage());
            return [];
        }
    }
}
