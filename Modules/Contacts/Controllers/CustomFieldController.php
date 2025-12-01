<?php

namespace Modules\Contacts\Controllers;

use App\Core\Application;
use Modules\Contacts\Models\ContactFieldDefinition;

class CustomFieldController
{
    public function index()
    {
        $app = Application::getInstance();
        $fields = ContactFieldDefinition::query()->orderBy('sort_order')->get();

        echo view('contacts/fields/index', [
            'title' => 'Champs Personnalisés',
            'fields' => $fields
        ]);
    }

    public function create()
    {
        $app = Application::getInstance();

        echo view('contacts/fields/create', [
            'title' => 'Nouveau Champ Personnalisé'
        ]);
    }

    public function store()
    {
        try {
            $name = sanitize($_POST['name'] ?? '', 'string');
            $type = sanitize($_POST['type'] ?? 'text', 'string');

            if (empty($name)) {
                flash('error', 'Le nom du champ est requis');
                redirect('/admin/contacts/fields/create');
                return;
            }

            $field = new ContactFieldDefinition();
            $field->name = $name;
            $field->slug = sanitize($_POST['slug'] ?? '', 'string') ?: ContactFieldDefinition::generateSlug($name);
            $field->type = $type;
            $field->is_required = isset($_POST['is_required']) ? 1 : 0;
            $field->default_value = sanitize($_POST['default_value'] ?? '', 'string');
            $field->placeholder = sanitize($_POST['placeholder'] ?? '', 'string');
            $field->help_text = sanitize($_POST['help_text'] ?? '', 'string');
            $field->sort_order = (int)($_POST['order'] ?? 0);

            // Handle options for select type
            if ($type === 'select' && !empty($_POST['options'])) {
                $options = array_map('trim', explode(',', $_POST['options']));
                $field->setOptions($options);
            }

            $field->save();

            flash('success', 'Champ personnalisé créé avec succès');
            redirect('/admin/contacts/fields');
        } catch (\Exception $e) {
            flash('error', 'Erreur: ' . $e->getMessage());
            redirect('/admin/contacts/fields/create');
        }
    }

    public function edit(array $params = [])
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            redirect('/admin/contacts/fields');
            return;
        }

        $field = ContactFieldDefinition::find($id);
        if (!$field) {
            flash('error', 'Champ introuvable');
            redirect('/admin/contacts/fields');
            return;
        }

        echo view('contacts/fields/edit', [
            'title' => 'Modifier Champ Personnalisé',
            'field' => $field
        ]);
    }

    public function update(array $params = [])
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            redirect('/admin/contacts/fields');
            return;
        }

        $field = ContactFieldDefinition::find($id);
        if (!$field) {
            flash('error', 'Champ introuvable');
            redirect('/admin/contacts/fields');
            return;
        }

        try {
            $name = sanitize($_POST['name'] ?? '', 'string');
            $type = sanitize($_POST['type'] ?? 'text', 'string');

            if (empty($name)) {
                flash('error', 'Le nom du champ est requis');
                redirect('/admin/contacts/fields/' . $id . '/edit');
                return;
            }

            $field->name = $name;
            $field->type = $type;
            $field->is_required = isset($_POST['is_required']) ? 1 : 0;
            $field->default_value = sanitize($_POST['default_value'] ?? '', 'string');
            $field->placeholder = sanitize($_POST['placeholder'] ?? '', 'string');
            $field->help_text = sanitize($_POST['help_text'] ?? '', 'string');
            $field->sort_order = (int)($_POST['order'] ?? 0);

            // Handle options for select type
            if ($type === 'select' && !empty($_POST['options'])) {
                $options = array_map('trim', explode(',', $_POST['options']));
                $field->setOptions($options);
            }

            $field->save();

            flash('success', 'Champ personnalisé mis à jour avec succès');
            redirect('/admin/contacts/fields');
        } catch (\Exception $e) {
            flash('error', 'Erreur: ' . $e->getMessage());
            redirect('/admin/contacts/fields/' . $id . '/edit');
        }
    }

    public function delete(array $params = [])
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            redirect('/admin/contacts/fields');
            return;
        }

        $field = ContactFieldDefinition::find($id);
        if ($field) {
            $field->delete();
            flash('success', 'Champ personnalisé supprimé avec succès');
        }

        redirect('/admin/contacts/fields');
    }
}
