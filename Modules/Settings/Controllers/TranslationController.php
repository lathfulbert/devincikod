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
        $language = $_GET['language'] ?? 'fr';

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

        $key = $_POST['key'] ?? null;
        $value = $_POST['value'] ?? null;
        $language = $_POST['language'] ?? 'fr';
        $module = $_POST['module'] ?? 'general';

        if (empty($key) || empty($value)) {
            $_SESSION['flash']['error'] = 'La clé et la valeur sont requises.';
            redirect('/admin/settings/translations/create');
        }

        Translation::setTranslation($key, $value, $language, $module);

        $_SESSION['flash']['success'] = 'Traduction créée avec succès.';
        redirect('/admin/settings/translations');
    }

    public function edit($params)
    {
        $app = Application::getInstance();
        $id = $params['id'] ?? null;

        $translation = Translation::find($id);

        if (!$translation) {
            $_SESSION['flash']['error'] = 'Traduction introuvable.';
            redirect('/admin/settings/translations');
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
        $id = $params['id'] ?? null;

        $translation = Translation::find($id);

        if (!$translation) {
            $_SESSION['flash']['error'] = 'Traduction introuvable.';
            redirect('/admin/settings/translations');
        }

        // Save to history
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            TranslationHistory::create([
                'translation_id' => $id,
                'old_value' => $translation['value'],
                'new_value' => $_POST['value'] ?? '',
                'changed_by' => $userId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        Translation::where('id', $id)->update([
            'value' => $_POST['value'] ?? '',
            'module' => $_POST['module'] ?? 'general',
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $_SESSION['flash']['success'] = 'Traduction mise à jour avec succès.';
        redirect('/admin/settings/translations');
    }

    public function delete($params)
    {
        $app = Application::getInstance();
        $id = $params['id'] ?? null;

        Translation::where('id', $id)->delete();

        $_SESSION['flash']['success'] = 'Traduction supprimée avec succès.';
        redirect('/admin/settings/translations');
    }

    public function import()
    {
        $app = Application::getInstance();

        if (!isset($_FILES['translation_file'])) {
            $_SESSION['flash']['error'] = 'Aucun fichier sélectionné.';
            redirect('/admin/settings/translations');
        }

        $file = $_FILES['translation_file'];
        $json = file_get_contents($file['tmp_name']);
        $data = json_decode($json, true);

        if (!$data) {
            $_SESSION['flash']['error'] = 'Fichier JSON invalide.';
            redirect('/admin/settings/translations');
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

        $_SESSION['flash']['success'] = "$count traductions importées avec succès.";
        redirect('/admin/settings/translations');
    }

    public function export()
    {
        $app = Application::getInstance();
        $language = $_GET['language'] ?? 'fr';

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
