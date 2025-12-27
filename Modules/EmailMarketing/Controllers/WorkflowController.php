<?php

namespace Modules\EmailMarketing\Controllers;

use Modules\EmailMarketing\Models\Workflow;
use Modules\EmailMarketing\Models\WorkflowExecution;
use Modules\EmailMarketing\Models\EmailTemplate;
use Modules\EmailMarketing\Services\MultiChannelService;
use Modules\EmailMarketing\Services\EmailSenderService;
use Modules\EmailMarketing\Services\EmailGatewayFactory;

/**
 * Gestion des workflows multicanal
 */
class WorkflowController
{
    protected MultiChannelService $multiChannelService;

    public function __construct()
    {
        $gatewayFactory = new EmailGatewayFactory();
        $emailSender = new EmailSenderService($gatewayFactory);
        $this->multiChannelService = new MultiChannelService($emailSender);

        // Injection du service SMS (à adapter selon votre architecture)
        // $smsService = app()->get('sms.sender');
        // $this->multiChannelService->setSmsService($smsService);
    }

    /**
     * Liste des workflows
     */
    public function index()
    {
        $status = $_GET['status'] ?? null;

        $query = Workflow::query()->orderBy('created_at', 'DESC');

        if ($status) {
            $query->where('status', $status);
        }

        $workflows = $query->get();

        return view('emailmarketing/workflows/index', [
            'title' => 'Workflows',
            'workflows' => $workflows,
            'currentStatus' => $status
        ]);
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $templates = EmailTemplate::where('is_active', true)->get();

        return view('emailmarketing/workflows/create', [
            'title' => 'Create Workflow',
            'templates' => $templates
        ]);
    }

    /**
     * Enregistrer un nouveau workflow
     */
    public function store()
    {
        $data = $_POST;

        // Validation
        if (empty($data['name'])) {
            $_SESSION['flash']['danger'][] = 'Name is required';
            redirect('/admin/email-marketing/workflows/create');
            exit;
        }

        // Parser les steps depuis le formulaire
        $steps = $this->parseSteps($data);

        if (empty($steps)) {
            $_SESSION['flash']['danger'][] = 'At least one step is required';
            redirect('/admin/email-marketing/workflows/create');
            exit;
        }

        $userId = $_SESSION['user_id'] ?? null;

        $workflow = Workflow::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'trigger_type' => $data['trigger_type'] ?? 'manual',
            'trigger_config' => isset($data['trigger_config']) ? json_encode($data['trigger_config']) : null,
            'steps' => json_encode($steps),
            'status' => 'active',
            'created_by' => $userId
        ]);

        $_SESSION['flash']['success'][] = 'Workflow created successfully';
        redirect('/admin/email-marketing/workflows/' . $workflow->id);
        exit;
    }

    /**
     * Afficher un workflow
     */
    public function show()
    {
        $id = $_GET['id'] ?? null;
        $workflow = Workflow::find($id);

        if (!$workflow) {
            $_SESSION['flash']['danger'][] = 'Workflow not found';
            redirect('/admin/email-marketing/workflows');
            exit;
        }

        // Récupérer les exécutions récentes
        $executions = WorkflowExecution::where('workflow_id', $workflow->id)
            ->orderBy('created_at', 'DESC')
            ->limit(20)
            ->get();

        return view('emailmarketing/workflows/show', [
            'title' => 'Workflow: ' . $workflow->name,
            'workflow' => $workflow,
            'executions' => $executions
        ]);
    }

    /**
     * Formulaire d'édition
     */
    public function edit()
    {
        $id = $_GET['id'] ?? null;
        $workflow = Workflow::find($id);

        if (!$workflow) {
            $_SESSION['flash']['danger'][] = 'Workflow not found';
            redirect('/admin/email-marketing/workflows');
            exit;
        }

        $templates = EmailTemplate::where('is_active', true)->get();

        return view('emailmarketing/workflows/edit', [
            'title' => 'Edit Workflow: ' . $workflow->name,
            'workflow' => $workflow,
            'templates' => $templates
        ]);
    }

    /**
     * Mettre à jour un workflow
     */
    public function update()
    {
        $id = $_GET['id'] ?? null;
        $workflow = Workflow::find($id);

        if (!$workflow) {
            $_SESSION['flash']['danger'][] = 'Workflow not found';
            redirect('/admin/email-marketing/workflows');
            exit;
        }

        $data = $_POST;

        // Parser les steps
        $steps = $this->parseSteps($data);

        $workflow->update([
            'name' => $data['name'] ?? $workflow->name,
            'description' => $data['description'] ?? $workflow->description,
            'trigger_type' => $data['trigger_type'] ?? $workflow->trigger_type,
            'trigger_config' => isset($data['trigger_config']) ? json_encode($data['trigger_config']) : $workflow->trigger_config,
            'steps' => !empty($steps) ? json_encode($steps) : $workflow->steps
        ]);

        $_SESSION['flash']['success'][] = 'Workflow updated successfully';
        redirect('/admin/email-marketing/workflows/' . $workflow->id);
        exit;
    }

    /**
     * Activer un workflow
     */
    public function activate()
    {
        $id = $_GET['id'] ?? null;
        $workflow = Workflow::find($id);

        if (!$workflow) {
            $_SESSION['flash']['danger'][] = 'Workflow not found';
            redirect('/admin/email-marketing/workflows');
            exit;
        }

        $workflow->activate();

        $_SESSION['flash']['success'][] = 'Workflow activated';
        redirect('/admin/email-marketing/workflows/' . $workflow->id);
        exit;
    }

    /**
     * Mettre en pause un workflow
     */
    public function pause()
    {
        $id = $_GET['id'] ?? null;
        $workflow = Workflow::find($id);

        if (!$workflow) {
            $_SESSION['flash']['danger'][] = 'Workflow not found';
            redirect('/admin/email-marketing/workflows');
            exit;
        }

        $workflow->pause();

        $_SESSION['flash']['success'][] = 'Workflow paused';
        redirect('/admin/email-marketing/workflows/' . $workflow->id);
        exit;
    }

    /**
     * Exécuter un workflow manuellement
     */
    public function execute()
    {
        $id = $_GET['id'] ?? null;
        $workflow = Workflow::find($id);

        if (!$workflow) {
            $_SESSION['flash']['danger'][] = 'Workflow not found';
            redirect('/admin/email-marketing/workflows');
            exit;
        }

        $contactIds = $_POST['contact_ids'] ?? [];

        if (empty($contactIds)) {
            $_SESSION['flash']['danger'][] = 'Please select contacts';
            redirect('/admin/email-marketing/workflows/' . $workflow->id);
            exit;
        }

        // Charger les contacts
        $db = \App\Core\Database\Database::getInstance();
        $placeholders = implode(',', array_fill(0, count($contactIds), '?'));
        $contacts = $db->query(
            "SELECT * FROM contacts WHERE id IN ($placeholders)",
            $contactIds
        )->fetchAll();

        if (empty($contacts)) {
            $_SESSION['flash']['danger'][] = 'No valid contacts found';
            redirect('/admin/email-marketing/workflows/' . $workflow->id);
            exit;
        }

        $successCount = 0;
        $failCount = 0;

        foreach ($contacts as $contact) {
            try {
                $result = $this->multiChannelService->executeWorkflow($workflow, $contact);

                if ($result['success']) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            } catch (\Exception $e) {
                $failCount++;
            }
        }

        $_SESSION['flash']['success'][] = "Workflow executed for {$successCount} contact(s). {$failCount} failed.";
        redirect('/admin/email-marketing/workflows/' . $workflow->id);
        exit;
    }

    /**
     * Supprimer un workflow
     */
    public function delete()
    {
        $id = $_GET['id'] ?? null;
        $workflow = Workflow::find($id);

        if (!$workflow) {
            $_SESSION['flash']['danger'][] = 'Workflow not found';
            redirect('/admin/email-marketing/workflows');
            exit;
        }

        // Archiver plutôt que supprimer si des exécutions existent
        $executionCount = WorkflowExecution::where('workflow_id', $workflow->id)->count();

        if ($executionCount > 0) {
            $workflow->archive();
            $_SESSION['flash']['success'][] = 'Workflow archived (has execution history)';
        } else {
            $workflow->delete();
            $_SESSION['flash']['success'][] = 'Workflow deleted successfully';
        }

        redirect('/admin/email-marketing/workflows');
        exit;
    }

    /**
     * Parser les steps depuis le formulaire
     */
    protected function parseSteps(array $data): array
    {
        $steps = [];

        // Format attendu: step[0][channel], step[0][template_id], etc.
        if (isset($data['step']) && is_array($data['step'])) {
            foreach ($data['step'] as $index => $step) {
                if (!empty($step['channel'])) {
                    $steps[] = [
                        'order' => $index + 1,
                        'channel' => $step['channel'],
                        'template_id' => $step['template_id'] ?? null,
                        'subject' => $step['subject'] ?? null,
                        'message' => $step['message'] ?? null,
                        'delay' => (int)($step['delay'] ?? 0),
                        'stop_on_failure' => isset($step['stop_on_failure']) ? true : false
                    ];
                }
            }
        }

        return $steps;
    }
}
