<?php

namespace Modules\Contacts\Controllers;

use App\Core\Application;
use Modules\Contacts\Models\Contact;
use Modules\Contacts\Models\ContactFieldDefinition;
use Modules\Contacts\Services\ContactService;

class ContactController
{
    public function __construct(protected ContactService $contactService) {}

    public function index()
    {
        $app = Application::getInstance();

        // Get filters
        $filters = [
            'search' => $_GET['search'] ?? null,
            'is_active' => $_GET['is_active'] ?? null,
        ];

        $contacts = $this->contactService->search($filters);
        $fieldDefinitions = ContactFieldDefinition::query()->orderBy('sort_order')->get();

        return view('contacts/index', [
            'title' => 'Contacts',
            'contacts' => $contacts,
            'fieldDefinitions' => $fieldDefinitions,
            'filters' => $filters
        ]);
    }

    public function create()
    {
        $app = Application::getInstance();
        $fieldDefinitions = ContactFieldDefinition::query()->orderBy('sort_order')->get();

        return view('contacts/create', [
            'title' => 'Nouveau Contact',
            'fieldDefinitions' => $fieldDefinitions
        ]);
    }

    public function store()
    {
        try {
            $data = [
                'phone' => sanitize($_POST['phone'] ?? '', 'string'),
                'first_name' => sanitize($_POST['first_name'] ?? '', 'string'),
                'last_name' => sanitize($_POST['last_name'] ?? '', 'string'),
                'email' => sanitize($_POST['email'] ?? '', 'string'),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'custom_fields' => []
            ];

            // Collect custom fields
            if (!empty($_POST['custom_fields'])) {
                foreach ($_POST['custom_fields'] as $slug => $value) {
                    $data['custom_fields'][$slug] = sanitize($value, 'string');
                }
            }

            $this->contactService->create($data);

            flash('success', 'Contact créé avec succès');
            redirect('/admin/contacts');
        } catch (\Exception $e) {
            flash('error', 'Erreur: ' . $e->getMessage());
            redirect('/admin/contacts/create');
        }
    }

    public function edit(array $params = [])
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            redirect('/admin/contacts');
            return;
        }

        $contact = Contact::find($id);
        if (!$contact) {
            flash('error', 'Contact introuvable');
            redirect('/admin/contacts');
            return;
        }

        $app = Application::getInstance();
        $fieldDefinitions = ContactFieldDefinition::query()->orderBy('sort_order')->get();

        return view('contacts/edit', [
            'title' => 'Modifier Contact',
            'contact' => $contact,
            'fieldDefinitions' => $fieldDefinitions
        ]);
    }

    public function update(array $params = [])
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            redirect('/admin/contacts');
            return;
        }

        $contact = Contact::find($id);
        if (!$contact) {
            flash('error', 'Contact introuvable');
            redirect('/admin/contacts');
            return;
        }

        try {
            $data = [
                'phone' => sanitize($_POST['phone'] ?? '', 'string'),
                'first_name' => sanitize($_POST['first_name'] ?? '', 'string'),
                'last_name' => sanitize($_POST['last_name'] ?? '', 'string'),
                'email' => sanitize($_POST['email'] ?? '', 'string'),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'custom_fields' => []
            ];

            // Collect custom fields
            if (!empty($_POST['custom_fields'])) {
                foreach ($_POST['custom_fields'] as $slug => $value) {
                    $data['custom_fields'][$slug] = sanitize($value, 'string');
                }
            }

            $this->contactService->update($contact, $data);

            flash('success', 'Contact mis à jour avec succès');
            redirect('/admin/contacts');
        } catch (\Exception $e) {
            flash('error', 'Erreur: ' . $e->getMessage());
            redirect('/admin/contacts/' . $id . '/edit');
        }
    }

    public function delete(array $params = [])
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            redirect('/admin/contacts');
            return;
        }

        $contact = Contact::find($id);
        if ($contact) {
            $this->contactService->delete($contact);
            flash('success', 'Contact supprimé avec succès');
        }

        redirect('/admin/contacts');
    }
}
