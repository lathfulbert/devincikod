<?php

namespace Modules\Settings\Controllers;

use App\Core\Application;
use Modules\Settings\Models\Translation;
use Modules\Settings\Models\TranslationHistory;

class TranslationController
{
    public function index()
    {
        $app = Application::getInstance();
        $language = $app->request->get('language', 'fr');

        $translations = Translation::where('language', $language)
            ->orderBy('key', 'ASC')
            ->get();

        $languages = ['fr' => 'Français', 'en' => 'English', 'ar' => 'العربية'];
        $modules = ['general', 'admin', 'auth', 'blog'];

        echo $app->view->render('settings/translations/index', [
            'title' => 'Gestion des Traductions',
            'translations' => $translations,
            'languages' => $languages,
            'modules' => $modules,
            'current_language' => $language,
        ]);
    }

    public function create()
    {
        $app = Application::getInstance();

        $languages = ['fr' => 'Français', 'en' => 'English', 'ar' => 'العربية'];
        $modules = ['general', 'admin', 'auth', 'blog'];

        echo $app->view->render('settings/translations/create', [
            'title' => 'Nouvelle Traduction',
            'languages' => $languages,
            'modules' => $modules,
        ]);
    }

    public function store()
    {
        $app = Application::getInstance();
        $request = $app->request;

        $key = $request->post('key');
        $value = $request->post('value');
        $language = $request->post('language', 'fr');
        $module = $request->post('module', 'general');

        if (empty($key) || empty($value)) {
            $app->session->setFlash('error', 'La clé et la valeur sont requises.');
            return $app->redirect('/admin/settings/translations/create');
        }

        Translation::setTranslation($key, $value, $language, $module);

        $app->session->setFlash('success', 'Traduction créée avec succès.');
        return $app->redirect('/admin/settings/translations');
    }

    public function edit($params)
    {
        $app = Application::getInstance();
        $id = $params['id'] ?? null;

        $translation = Translation::find($id);

        if (!$translation) {
            $app->session->setFlash('error', 'Traduction introuvable.');
            return $app->redirect('/admin/settings/translations');
        }

        $languages = ['fr' => 'Français', 'en' => 'English', 'ar' => 'العربية'];
        $modules = ['general', 'admin', 'auth', 'blog'];

        echo $app->view->render('settings/translations/edit', [
            'title' => 'Modifier la Traduction',
            'translation' => $translation,
            'languages' => $languages,
            'modules' => $modules,
        ]);
    }

    public function update($params)
    {
        $app = Application::getInstance();
        $request = $app->request;
        $id = $params['id'] ?? null;

        $translation = Translation::find($id);

        if (!$translation) {
            $app->session->setFlash('error', 'Traduction introuvable.');
            return $app->redirect('/admin/settings/translations');
        }

        // Save to history
        $userId = $app->session->get('user_id');
        if ($userId) {
            TranslationHistory::create([
                'translation_id' => $id,
                'old_value' => $translation['value'],
                'new_value' => $request->post('value'),
                'changed_by' => $userId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        Translation::where('id', $id)->update([
            'value' => $request->post('value'),
            'module' => $request->post('module', 'general'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $app->session->setFlash('success', 'Traduction mise à jour avec succès.');
        return $app->redirect('/admin/settings/translations');
    }

    public function delete($params)
    {
        $app = Application::getInstance();
        $id = $params['id'] ?? null;

        Translation::where('id', $id)->delete();

        $app->session->setFlash('success', 'Traduction supprimée avec succès.');
        return $app->redirect('/admin/settings/translations');
    }

    public function import()
    {
        $app = Application::getInstance();

        if (!isset($_FILES['translation_file'])) {
            $app->session->setFlash('error', 'Aucun fichier sélectionné.');
            return $app->redirect('/admin/settings/translations');
        }

        $file = $_FILES['translation_file'];
        $json = file_get_contents($file['tmp_name']);
        $data = json_decode($json, true);

        if (!$data) {
            $app->session->setFlash('error', 'Fichier JSON invalide.');
            return $app->redirect('/admin/settings/translations');
        }

        $count = 0;
        foreach ($data as $item) {
            Translation::setTranslation(
                $item['key'],
                $item['value'],
                $item['language'] ?? 'fr',
                $item['module'] ?? 'general'
            );
            $count++;
        }

        $app->session->setFlash('success', "$count traductions importées avec succès.");
        return $app->redirect('/admin/settings/translations');
    }

    public function export()
    {
        $app = Application::getInstance();
        $language = $app->request->get('language', 'fr');

        $translations = Translation::where('language', $language)->get();

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="translations-' . $language . '-' . date('Y-m-d') . '.json"');

        echo json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function history()
    {
        $app = Application::getInstance();

        $history = TranslationHistory::orderBy('created_at', 'DESC')
            ->limit(100)
            ->get();

        echo $app->view->render('settings/translations/history', [
            'title' => 'Historique des Traductions',
            'history' => $history,
        ]);
    }
}
