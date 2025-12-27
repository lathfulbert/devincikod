<?php

namespace Modules\EmailMarketing\Controllers;

use Modules\EmailMarketing\Models\EmailTemplate;
use Modules\EmailMarketing\Services\EmailSenderService;
use Modules\EmailMarketing\Services\EmailGatewayFactory;

/**
 * Gestion des templates email
 */
class EmailTemplateController
{
    protected EmailSenderService $senderService;

    public function __construct()
    {
        $gatewayFactory = new EmailGatewayFactory();
        $this->senderService = new EmailSenderService($gatewayFactory);
    }

    /**
     * Liste des templates
     */
    public function index()
    {
        $category = $_GET['category'] ?? null;

        $query = EmailTemplate::query()->orderBy('created_at', 'DESC');

        if ($category) {
            $query->where('category', $category);
        }

        $templates = $query->get();

        // Catégories disponibles
        $rawCategories = EmailTemplate::selectRaw('DISTINCT category')
            ->whereNotNull('category')
            ->get();

        $categories = array_map(fn($item) => $item->category, $rawCategories);

        return view('emailmarketing/templates/index', [
            'title' => 'Email Templates',
            'templates' => $templates,
            'categories' => $categories,
            'currentCategory' => $category
        ]);
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        return view('emailmarketing/templates/create', [
            'title' => 'Create Email Template'
        ]);
    }

    /**
     * Enregistrer un nouveau template
     */
    public function store()
    {
        $data = $_POST;

        // Validation
        if (empty($data['name']) || empty($data['html_content'])) {
            $_SESSION['flash']['danger'][] = 'Name and content are required';
            redirect('/admin/email-marketing/templates/create');
            exit;
        }

        $userId = $_SESSION['user_id'] ?? null;

        $template = EmailTemplate::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'subject' => $data['subject'] ?? null,
            'type' => $data['type'] ?? 'html',
            'html_content' => $data['html_content'],
            'category' => $data['category'] ?? null,
            'tags' => isset($data['tags']) ? json_encode(explode(',', $data['tags'])) : null,
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'created_by' => $userId
        ]);

        $_SESSION['flash']['success'][] = 'Template created successfully';
        redirect('/admin/email-marketing/templates/' . $template->id);
        exit;
    }

    /**
     * Afficher un template
     */
    public function show()
    {
        $id = $_GET['id'] ?? null;
        $template = EmailTemplate::find($id);

        if (!$template) {
            $_SESSION['flash']['danger'][] = 'Template not found';
            redirect('/admin/email-marketing/templates');
            exit;
        }

        // Extraire les variables
        $variables = $template->getVariables();

        // Compteur d'utilisation
        $usageCount = $template->campaigns()->count();

        return view('emailmarketing/templates/show', [
            'title' => 'Template: ' . $template->name,
            'template' => $template,
            'variables' => $variables,
            'usageCount' => $usageCount
        ]);
    }

    /**
     * Formulaire d'édition
     */
    public function edit()
    {
        $id = $_GET['id'] ?? null;
        $template = EmailTemplate::find($id);

        if (!$template) {
            $_SESSION['flash']['danger'][] = 'Template not found';
            redirect('/admin/email-marketing/templates');
            exit;
        }

        return view('emailmarketing/templates/edit', [
            'title' => 'Edit Template: ' . $template->name,
            'template' => $template
        ]);
    }

    /**
     * Mettre à jour un template
     */
    public function update()
    {
        $id = $_GET['id'] ?? null;
        $template = EmailTemplate::find($id);

        if (!$template) {
            $_SESSION['flash']['danger'][] = 'Template not found';
            redirect('/admin/email-marketing/templates');
            exit;
        }

        $data = $_POST;

        $template->update([
            'name' => $data['name'] ?? $template->name,
            'description' => $data['description'] ?? $template->description,
            'subject' => $data['subject'] ?? $template->subject,
            'type' => $data['type'] ?? $template->type,
            'html_content' => $data['html_content'] ?? $template->html_content,
            'category' => $data['category'] ?? $template->category,
            'tags' => isset($data['tags']) ? json_encode(explode(',', $data['tags'])) : $template->tags,
            'is_active' => isset($data['is_active']) ? 1 : 0
        ]);

        $_SESSION['flash']['success'][] = 'Template updated successfully';
        redirect('/admin/email-marketing/templates/' . $template->id);
        exit;
    }

    /**
     * Dupliquer un template
     */
    public function duplicate()
    {
        $id = $_GET['id'] ?? null;
        $template = EmailTemplate::find($id);

        if (!$template) {
            $_SESSION['flash']['danger'][] = 'Template not found';
            redirect('/admin/email-marketing/templates');
            exit;
        }

        $newName = $_POST['name'] ?? ($template->name . ' (Copy)');
        $copy = $template->duplicate($newName);

        $_SESSION['flash']['success'][] = 'Template duplicated successfully';
        redirect('/admin/email-marketing/templates/' . $copy->id);
        exit;
    }

    /**
     * Supprimer un template
     */
    public function delete()
    {
        $id = $_GET['id'] ?? null;
        $template = EmailTemplate::find($id);

        if (!$template) {
            $_SESSION['flash']['danger'][] = 'Template not found';
            redirect('/admin/email-marketing/templates');
            exit;
        }

        // Vérifier si le template est utilisé
        $usageCount = $template->campaigns()->count();

        if ($usageCount > 0) {
            $_SESSION['flash']['danger'][] = "Cannot delete template. It is used by {$usageCount} campaign(s)";
            redirect('/admin/email-marketing/templates/' . $template->id);
            exit;
        }

        $template->delete();

        $_SESSION['flash']['success'][] = 'Template deleted successfully';
        redirect('/admin/email-marketing/templates');
        exit;
    }

    /**
     * Envoyer un email de test
     */
    public function sendTest()
    {
        $id = $_GET['id'] ?? null;
        $template = EmailTemplate::find($id);

        if (!$template) {
            $_SESSION['flash']['danger'][] = 'Template not found';
            redirect('/admin/email-marketing/templates');
            exit;
        }

        $testEmail = $_POST['test_email'] ?? null;

        if (!$testEmail) {
            $_SESSION['flash']['danger'][] = 'Test email address is required';
            redirect('/admin/email-marketing/templates/' . $template->id);
            exit;
        }

        // Données de test
        $testData = [
            'first_name' => $_POST['first_name'] ?? 'John',
            'last_name' => $_POST['last_name'] ?? 'Doe',
            'email' => $testEmail,
            'company' => $_POST['company'] ?? 'Test Company'
        ];

        try {
            $result = $this->senderService->sendTest($testEmail, $template, $testData);

            if ($result['success']) {
                $_SESSION['flash']['success'][] = "Test email sent successfully to {$testEmail}";
            } else {
                $_SESSION['flash']['danger'][] = "Failed to send test email: {$result['message']}";
            }
        } catch (\Exception $e) {
            $_SESSION['flash']['danger'][] = 'Error: ' . $e->getMessage();
        }

        redirect('/admin/email-marketing/templates/' . $template->id);
        exit;
    }

    /**
     * Prévisualiser un template
     */
    public function preview()
    {
        $id = $_GET['id'] ?? null;
        $template = EmailTemplate::find($id);

        if (!$template) {
            echo "Template not found";
            exit;
        }

        // Données de prévisualisation
        $previewData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'company' => 'Example Inc.'
        ];

        // Rendre le template avec les données de preview
        $html = $template->render($previewData);

        // Afficher directement le HTML
        echo $html;
        exit;
    }
}
