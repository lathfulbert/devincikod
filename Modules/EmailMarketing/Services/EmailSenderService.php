<?php

namespace Modules\EmailMarketing\Services;

use Modules\EmailMarketing\Models\EmailMessage;
use Modules\EmailMarketing\Models\EmailCampaign;
use Modules\EmailMarketing\Models\EmailTemplate;
use Modules\EmailMarketing\Models\CampaignLog;
use Modules\EmailMarketing\Gateways\EmailGatewayInterface;

/**
 * Service principal pour l'envoi d'emails
 */
class EmailSenderService
{
    protected EmailGatewayFactory $gatewayFactory;
    protected ?EmailGatewayInterface $gateway = null;

    public function __construct(EmailGatewayFactory $gatewayFactory)
    {
        $this->gatewayFactory = $gatewayFactory;
    }

    /**
     * Envoyer un email unique
     *
     * @param string $to Email destinataire
     * @param string $subject Sujet
     * @param string $html Contenu HTML
     * @param array $options Options (from, reply_to, campaign_id, user_id, etc.)
     * @return array Résultat de l'envoi
     */
    public function send(
        string $to,
        string $subject,
        string $html,
        array $options = []
    ): array {
        try {
            // 1. Récupérer le gateway
            $gatewayName = $options['gateway'] ?? null;
            $gateway = $this->getGateway($gatewayName);

            // 2. Préparer les options
            $from = $options['from'] ?? null;
            $fromName = $options['from_name'] ?? null;
            $replyTo = $options['reply_to'] ?? null;
            $userId = $options['user_id'] ?? null;
            $campaignId = $options['campaign_id'] ?? null;

            // 3. Calculer le coût (exemple: 0.001€ par email)
            $cost = $options['cost'] ?? 0.001;

            // 4. Créer le message en base AVANT l'envoi
            $messageId = uniqid('email_', true);
            $message = EmailMessage::create([
                'campaign_id' => $campaignId,
                'user_id' => $userId,
                'to_email' => $to,
                'to_name' => $options['to_name'] ?? null,
                'from_email' => $from,
                'from_name' => $fromName,
                'reply_to' => $replyTo,
                'subject' => $subject,
                'body_html' => $html,
                'body_text' => strip_tags($html),
                'gateway' => $gateway->getName(),
                'status' => 'pending',
                'message_id' => $messageId,
                'cost' => $cost,
                'metadata' => json_encode($options['metadata'] ?? [])
            ]);

            // 5. Envoyer via le gateway
            $result = $gateway->send($to, $subject, $html, [
                'from' => $from,
                'from_name' => $fromName,
                'reply_to' => $replyTo
            ]);

            // 6. Mettre à jour le message selon le résultat
            if ($result['success']) {
                $message->markAsSent($result['message_id'] ?? null);

                // 7. Logger dans campaign_logs (centralisé)
                if ($campaignId) {
                    CampaignLog::logEmail(
                        $campaignId,
                        'email',
                        $options['contact_id'] ?? 0,
                        $to,
                        [
                            'message_id' => $messageId,
                            'gateway' => $gateway->getName(),
                            'status' => 'sent',
                            'cost' => $cost,
                            'sent_at' => date('Y-m-d H:i:s')
                        ]
                    );

                    // 8. Incrémenter le compteur de la campagne
                    if ($campaign = EmailCampaign::find($campaignId)) {
                        $campaign->incrementSent();
                    }
                }

                return [
                    'success' => true,
                    'message_id' => $messageId,
                    'gateway_message_id' => $result['message_id'],
                    'message' => 'Email sent successfully',
                    'cost' => $cost
                ];
            } else {
                // Échec
                $message->markAsFailed($result['message'] ?? 'Unknown error');

                // Logger l'échec
                if ($campaignId) {
                    CampaignLog::logEmail(
                        $campaignId,
                        'email',
                        $options['contact_id'] ?? 0,
                        $to,
                        [
                            'message_id' => $messageId,
                            'gateway' => $gateway->getName(),
                            'status' => 'failed',
                            'cost' => 0,
                            'metadata' => ['error' => $result['message']]
                        ]
                    );

                    if ($campaign = EmailCampaign::find($campaignId)) {
                        $campaign->incrementFailed();
                    }
                }

                return [
                    'success' => false,
                    'message_id' => $messageId,
                    'message' => $result['message'],
                    'error' => $result['message']
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Envoyer des emails en masse (bulk)
     *
     * @param array $recipients [['email' => '...', 'name' => '...', 'data' => [...]], ...]
     * @param EmailTemplate|int $template Template ou ID
     * @param array $options Options globales
     * @return array Résultats
     */
    public function sendBulk(
        array $recipients,
        $template,
        array $options = []
    ): array {
        $results = [
            'total' => count($recipients),
            'sent' => 0,
            'failed' => 0,
            'results' => []
        ];

        // Charger le template si c'est un ID
        if (is_int($template)) {
            $template = EmailTemplate::find($template);
        }

        if (!$template) {
            return [
                'success' => false,
                'message' => 'Template not found',
                'results' => $results
            ];
        }

        $campaignId = $options['campaign_id'] ?? null;

        foreach ($recipients as $recipient) {
            $email = $recipient['email'];
            $name = $recipient['name'] ?? null;
            $data = $recipient['data'] ?? [];

            // Personnaliser le contenu
            $html = $template->render($data);
            $subject = $template->renderSubject($data);

            // Envoyer
            $result = $this->send($email, $subject, $html, array_merge($options, [
                'to_name' => $name,
                'contact_id' => $recipient['contact_id'] ?? null
            ]));

            $results['results'][] = $result;

            if ($result['success']) {
                $results['sent']++;
            } else {
                $results['failed']++;
            }
        }

        // Marquer la campagne comme complétée si tous envoyés
        if ($campaignId && $campaign = EmailCampaign::find($campaignId)) {
            if ($campaign->isCompleted()) {
                $campaign->markAsCompleted();
            }
        }

        return [
            'success' => $results['failed'] === 0,
            'message' => "Sent {$results['sent']}/{$results['total']} emails",
            'results' => $results
        ];
    }

    /**
     * Envoyer une campagne email complète
     *
     * @param EmailCampaign $campaign
     * @param array $contacts
     * @return array
     */
    public function sendCampaign(EmailCampaign $campaign, array $contacts): array
    {
        // Marquer la campagne comme démarrée
        $campaign->markAsStarted();

        // Préparer les destinataires
        $recipients = array_map(function ($contact) {
            return [
                'email' => $contact->email ?? $contact['email'],
                'name' => ($contact->first_name ?? $contact['first_name'] ?? '') . ' ' .
                         ($contact->last_name ?? $contact['last_name'] ?? ''),
                'contact_id' => $contact->id ?? $contact['id'],
                'data' => [
                    'first_name' => $contact->first_name ?? $contact['first_name'] ?? '',
                    'last_name' => $contact->last_name ?? $contact['last_name'] ?? '',
                    'email' => $contact->email ?? $contact['email']
                ]
            ];
        }, $contacts);

        // Envoyer en bulk
        $result = $this->sendBulk($recipients, $campaign->template_id, [
            'campaign_id' => $campaign->id,
            'from' => $campaign->from_email,
            'from_name' => $campaign->from_name,
            'reply_to' => $campaign->reply_to
        ]);

        return $result;
    }

    /**
     * Obtenir ou créer une instance de gateway
     */
    protected function getGateway(?string $gatewayName = null): EmailGatewayInterface
    {
        if ($this->gateway && !$gatewayName) {
            return $this->gateway;
        }

        $this->gateway = $this->gatewayFactory->make($gatewayName);
        return $this->gateway;
    }

    /**
     * Définir le gateway à utiliser
     */
    public function setGateway(string $gatewayName): self
    {
        $this->gateway = $this->gatewayFactory->make($gatewayName);
        return $this;
    }

    /**
     * Envoyer un email de test
     */
    public function sendTest(string $to, EmailTemplate $template, array $testData = []): array
    {
        $defaultData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => $to,
            'company' => 'Test Company'
        ];

        $data = array_merge($defaultData, $testData);

        return $this->send(
            $to,
            $template->renderSubject($data),
            $template->render($data),
            ['metadata' => ['test' => true]]
        );
    }
}
