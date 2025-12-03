<?php

namespace Modules\EmailMarketing\Services;

use Modules\EmailMarketing\Models\CampaignLog;
use Modules\EmailMarketing\Models\Workflow;
use Modules\EmailMarketing\Models\WorkflowExecution;
use Modules\EmailMarketing\Models\EmailTemplate;
use Modules\SmsCore\Services\SmsSenderService;

/**
 * Service d'orchestration multicanal (Email + SMS)
 * Le cœur de l'intégration entre les modules Email et SMS
 */
class MultiChannelService
{
    protected EmailSenderService $emailService;
    protected ?SmsSenderService $smsService = null;

    public function __construct(EmailSenderService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Définir le service SMS (injection via setter pour éviter dépendance circulaire)
     */
    public function setSmsService(SmsSenderService $smsService): void
    {
        $this->smsService = $smsService;
    }

    /**
     * Envoyer une campagne multicanal
     *
     * @param array $contacts Liste des contacts
     * @param array $channels ['email', 'sms']
     * @param array $config Configuration par canal
     * @return array Résultats détaillés
     */
    public function sendMultiChannelCampaign(
        array $contacts,
        array $channels,
        array $config
    ): array {
        $results = [
            'total_contacts' => count($contacts),
            'channels' => $channels,
            'email' => ['sent' => 0, 'failed' => 0, 'results' => []],
            'sms' => ['sent' => 0, 'failed' => 0, 'results' => []],
            'total_sent' => 0,
            'total_failed' => 0,
            'total_cost' => 0
        ];

        $campaignId = $config['campaign_id'] ?? null;
        $campaignType = $config['campaign_type'] ?? 'multichannel';

        foreach ($contacts as $contact) {
            $contactId = $contact->id ?? $contact['id'];
            $email = $contact->email ?? $contact['email'] ?? null;
            $phone = $contact->phone ?? $contact['phone'] ?? null;

            // Envoyer par Email
            if (in_array('email', $channels) && $email) {
                $emailResult = $this->sendEmail($contact, $config['email'] ?? [], $campaignId, $campaignType);
                $results['email']['results'][] = $emailResult;

                if ($emailResult['success']) {
                    $results['email']['sent']++;
                    $results['total_sent']++;
                } else {
                    $results['email']['failed']++;
                    $results['total_failed']++;
                }

                $results['total_cost'] += $emailResult['cost'] ?? 0;
            }

            // Envoyer par SMS
            if (in_array('sms', $channels) && $phone) {
                $smsResult = $this->sendSms($contact, $config['sms'] ?? [], $campaignId, $campaignType);
                $results['sms']['results'][] = $smsResult;

                if ($smsResult['success']) {
                    $results['sms']['sent']++;
                    $results['total_sent']++;
                } else {
                    $results['sms']['failed']++;
                    $results['total_failed']++;
                }

                $results['total_cost'] += $smsResult['cost'] ?? 0;
            }
        }

        $results['success'] = $results['total_failed'] === 0;

        return $results;
    }

    /**
     * Exécuter un workflow multicanal complet
     *
     * @param Workflow $workflow
     * @param object|array $contact
     * @return array
     */
    public function executeWorkflow(Workflow $workflow, $contact): array
    {
        if (!$workflow->isActive()) {
            return [
                'success' => false,
                'message' => 'Workflow is not active'
            ];
        }

        $contactId = $contact->id ?? $contact['id'];
        $steps = $workflow->getSteps();

        // Créer l'exécution
        $execution = WorkflowExecution::create([
            'workflow_id' => $workflow->id,
            'contact_id' => $contactId,
            'status' => 'pending',
            'total_steps' => count($steps),
            'current_step' => 0
        ]);

        $execution->start();

        $results = [
            'execution_id' => $execution->id,
            'steps_completed' => 0,
            'steps_failed' => 0,
            'steps' => []
        ];

        try {
            foreach ($steps as $index => $step) {
                $stepNumber = $index + 1;

                // Appliquer le délai avant exécution (sauf pour la première étape)
                if ($stepNumber > 1 && isset($step['delay']) && $step['delay'] > 0) {
                    // Pour production: utiliser une queue job avec délai
                    // Pour développement: simuler avec sleep (à éviter en production!)
                    // sleep($step['delay']);

                    // Planifier la prochaine étape
                    $execution->update([
                        'next_step_at' => date('Y-m-d H:i:s', time() + $step['delay'])
                    ]);

                    // En production, arrêter ici et laisser un job cron continuer
                    break;
                }

                // Exécuter l'étape selon le canal
                $stepResult = $this->executeWorkflowStep($contact, $step, [
                    'workflow_id' => $workflow->id,
                    'execution_id' => $execution->id,
                    'step_number' => $stepNumber
                ]);

                $results['steps'][] = $stepResult;

                if ($stepResult['success']) {
                    $results['steps_completed']++;
                } else {
                    $results['steps_failed']++;

                    // Arrêter le workflow en cas d'échec (optionnel)
                    if ($step['stop_on_failure'] ?? false) {
                        $execution->fail("Step {$stepNumber} failed: {$stepResult['message']}");
                        break;
                    }
                }

                // Mettre à jour la progression
                $execution->nextStep($step['delay'] ?? 0);
            }

            // Marquer comme complété si toutes les étapes sont faites
            if ($execution->current_step >= $execution->total_steps) {
                $execution->complete();
            }

            $results['success'] = $results['steps_failed'] === 0;

            return $results;
        } catch (\Exception $e) {
            $execution->fail($e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'results' => $results
            ];
        }
    }

    /**
     * Exécuter une étape de workflow
     */
    protected function executeWorkflowStep($contact, array $step, array $context): array
    {
        $channel = $step['channel'] ?? 'email';

        return match ($channel) {
            'email' => $this->executeEmailStep($contact, $step, $context),
            'sms' => $this->executeSmsStep($contact, $step, $context),
            default => [
                'success' => false,
                'message' => "Unknown channel: {$channel}"
            ]
        };
    }

    /**
     * Exécuter une étape email
     */
    protected function executeEmailStep($contact, array $step, array $context): array
    {
        $email = $contact->email ?? $contact['email'] ?? null;

        if (!$email) {
            return [
                'success' => false,
                'channel' => 'email',
                'message' => 'No email address for contact'
            ];
        }

        // Charger le template
        $templateId = $step['template_id'] ?? null;
        $subject = $step['subject'] ?? 'Notification';
        $html = $step['html'] ?? null;

        if ($templateId) {
            $template = EmailTemplate::find($templateId);
            if ($template) {
                $data = $this->prepareContactData($contact);
                $html = $template->render($data);
                $subject = $template->renderSubject($data);
            }
        }

        if (!$html) {
            return [
                'success' => false,
                'channel' => 'email',
                'message' => 'No email content provided'
            ];
        }

        // Envoyer l'email
        return $this->sendEmail($contact, [
            'subject' => $subject,
            'html' => $html,
            'from' => $step['from'] ?? null,
            'from_name' => $step['from_name'] ?? null
        ], $context['workflow_id'], 'workflow');
    }

    /**
     * Exécuter une étape SMS
     */
    protected function executeSmsStep($contact, array $step, array $context): array
    {
        $phone = $contact->phone ?? $contact['phone'] ?? null;

        if (!$phone) {
            return [
                'success' => false,
                'channel' => 'sms',
                'message' => 'No phone number for contact'
            ];
        }

        $message = $step['message'] ?? '';

        // Personnaliser le message
        $data = $this->prepareContactData($contact);
        foreach ($data as $key => $value) {
            $message = str_replace('{{' . $key . '}}', $value, $message);
        }

        // Envoyer le SMS
        return $this->sendSms($contact, [
            'message' => $message,
            'sender_id' => $step['sender_id'] ?? null
        ], $context['workflow_id'], 'workflow');
    }

    /**
     * Envoyer un email (wrapper)
     */
    protected function sendEmail($contact, array $config, ?int $campaignId, string $campaignType): array
    {
        $email = $contact->email ?? $contact['email'];
        $contactId = $contact->id ?? $contact['id'];

        $result = $this->emailService->send(
            $email,
            $config['subject'] ?? 'Notification',
            $config['html'] ?? $config['content'] ?? '',
            [
                'campaign_id' => $campaignId,
                'contact_id' => $contactId,
                'from' => $config['from'] ?? null,
                'from_name' => $config['from_name'] ?? null,
                'reply_to' => $config['reply_to'] ?? null
            ]
        );

        // Logger dans campaign_logs
        if ($campaignId) {
            CampaignLog::logEmail(
                $campaignId,
                $campaignType,
                $contactId,
                $email,
                [
                    'message_id' => $result['message_id'] ?? null,
                    'gateway' => 'email',
                    'status' => $result['success'] ? 'sent' : 'failed',
                    'cost' => $result['cost'] ?? 0,
                    'sent_at' => $result['success'] ? date('Y-m-d H:i:s') : null,
                    'metadata' => ['error' => $result['error'] ?? null]
                ]
            );
        }

        return array_merge($result, ['channel' => 'email']);
    }

    /**
     * Envoyer un SMS (wrapper vers module SMS)
     */
    protected function sendSms($contact, array $config, ?int $campaignId, string $campaignType): array
    {
        if (!$this->smsService) {
            return [
                'success' => false,
                'channel' => 'sms',
                'message' => 'SMS service not available'
            ];
        }

        $phone = $contact->phone ?? $contact['phone'];
        $contactId = $contact->id ?? $contact['id'];

        try {
            // Appeler le service SMS existant
            $result = $this->smsService->send(
                $phone,
                $config['message'] ?? '',
                $config['sender_id'] ?? null,
                [
                    'user_id' => $config['user_id'] ?? null,
                    'gateway_name' => $config['gateway'] ?? 'default'
                ]
            );

            // Logger dans campaign_logs
            if ($campaignId) {
                CampaignLog::logSms(
                    $campaignId,
                    $campaignType,
                    $contactId,
                    $phone,
                    [
                        'message_id' => $result['gateway_response']['message_id'] ?? null,
                        'gateway' => $config['gateway'] ?? 'default',
                        'status' => $result['success'] ? 'sent' : 'failed',
                        'cost' => $result['cost'] ?? 0,
                        'sent_at' => $result['success'] ? date('Y-m-d H:i:s') : null,
                        'metadata' => $result
                    ]
                );
            }

            return array_merge($result, ['channel' => 'sms']);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'channel' => 'sms',
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Préparer les données du contact pour personnalisation
     */
    protected function prepareContactData($contact): array
    {
        return [
            'first_name' => $contact->first_name ?? $contact['first_name'] ?? '',
            'last_name' => $contact->last_name ?? $contact['last_name'] ?? '',
            'email' => $contact->email ?? $contact['email'] ?? '',
            'phone' => $contact->phone ?? $contact['phone'] ?? '',
            'full_name' => trim(
                ($contact->first_name ?? $contact['first_name'] ?? '') . ' ' .
                ($contact->last_name ?? $contact['last_name'] ?? '')
            )
        ];
    }

    /**
     * Obtenir les statistiques d'une campagne multicanal
     */
    public function getCampaignStats(int $campaignId): array
    {
        return CampaignLog::getStatsByCampaign($campaignId);
    }

    /**
     * Comparer les performances des canaux
     */
    public function compareChannels(int $campaignId): array
    {
        $emailStats = CampaignLog::getStatsByChannel('email');
        $smsStats = CampaignLog::getStatsByChannel('sms');

        return [
            'email' => $emailStats,
            'sms' => $smsStats,
            'comparison' => [
                'delivery_rate' => [
                    'email' => $this->calculateRate($emailStats['delivered'], $emailStats['total']),
                    'sms' => $this->calculateRate($smsStats['delivered'], $smsStats['total'])
                ],
                'engagement_rate' => [
                    'email' => $this->calculateRate($emailStats['opened'], $emailStats['delivered']),
                    'sms' => $this->calculateRate($smsStats['opened'], $smsStats['delivered'])
                ],
                'total_cost' => [
                    'email' => $emailStats['total_cost'],
                    'sms' => $smsStats['total_cost'],
                    'total' => $emailStats['total_cost'] + $smsStats['total_cost']
                ]
            ]
        ];
    }

    /**
     * Calculer un taux en pourcentage
     */
    protected function calculateRate(int $numerator, int $denominator): float
    {
        if ($denominator == 0) {
            return 0;
        }

        return round(($numerator / $denominator) * 100, 2);
    }
}
