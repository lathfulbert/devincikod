<?php

namespace Modules\EmailMarketing\Controllers;

use Modules\EmailMarketing\Models\EmailMessage;
use Modules\EmailMarketing\Models\EmailLog;
use Modules\EmailMarketing\Models\EmailCampaign;

/**
 * Controller pour le tracking des ouvertures et clics
 * Webhooks appelés depuis les emails envoyés
 */
class TrackingController
{
    /**
     * Tracker l'ouverture d'un email
     * GET /email/track/open/{messageId}
     *
     * Généralement inclus via un pixel 1x1 transparent dans l'email
     */
    public function trackOpen()
    {
        $messageId = $_GET['messageId'] ?? null;

        if (!$messageId) {
            $this->returnTransparentPixel();
            exit;
        }

        $message = EmailMessage::where('message_id', $messageId)->first();

        if (!$message) {
            $this->returnTransparentPixel();
            exit;
        }

        // Marquer comme ouvert
        $message->markAsOpened();

        // Logger l'événement
        EmailLog::logEvent(
            $message->id,
            'opened',
            [
                'ip' => $this->getClientIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'location' => $this->getLocationFromIp($this->getClientIp())
            ]
        );

        // Incrémenter le compteur de la campagne
        if ($message->campaign_id) {
            $campaign = EmailCampaign::find($message->campaign_id);
            if ($campaign) {
                $campaign->incrementOpened();
            }
        }

        // Retourner un pixel transparent 1x1
        $this->returnTransparentPixel();
        exit;
    }

    /**
     * Tracker le clic sur un lien
     * GET /email/track/click/{messageId}?url=...
     */
    public function trackClick()
    {
        $messageId = $_GET['messageId'] ?? null;
        $targetUrl = $_GET['url'] ?? null;

        if (!$messageId || !$targetUrl) {
            // Rediriger vers la page d'accueil si données manquantes
            header('Location: /');
            exit;
        }

        $message = EmailMessage::where('message_id', $messageId)->first();

        if (!$message) {
            // Rediriger vers l'URL cible même si le message n'existe pas
            header('Location: ' . $targetUrl);
            exit;
        }

        // Marquer comme cliqué
        $message->markAsClicked();

        // Logger l'événement avec l'URL cliquée
        EmailLog::logEvent(
            $message->id,
            'clicked',
            [
                'ip' => $this->getClientIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'location' => $this->getLocationFromIp($this->getClientIp()),
                'link' => $targetUrl
            ]
        );

        // Incrémenter le compteur de la campagne
        if ($message->campaign_id) {
            $campaign = EmailCampaign::find($message->campaign_id);
            if ($campaign) {
                $campaign->incrementClicked();
            }
        }

        // Rediriger vers l'URL cible
        header('Location: ' . $targetUrl);
        exit;
    }

    /**
     * Retourner un pixel transparent 1x1
     */
    protected function returnTransparentPixel(): void
    {
        header('Content-Type: image/gif');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        // GIF transparent 1x1 (43 bytes)
        echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    }

    /**
     * Obtenir l'IP du client
     */
    protected function getClientIp(): string
    {
        $ipKeys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER)) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);

                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Obtenir la localisation depuis l'IP (simplifié)
     * En production, utiliser une API de géolocalisation comme MaxMind ou IP2Location
     */
    protected function getLocationFromIp(string $ip): ?string
    {
        // TODO: Implémenter avec une vraie API de géolocalisation
        // Exemple avec API gratuite:
        // $response = file_get_contents("http://ip-api.com/json/{$ip}");
        // $data = json_decode($response, true);
        // return $data['country'] . ', ' . $data['city'];

        return null; // Pour l'instant
    }
}
