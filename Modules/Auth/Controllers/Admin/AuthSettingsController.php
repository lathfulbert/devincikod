<?php
namespace Modules\Auth\Controllers\Admin;

use Modules\Auth\Models\AuthSetting;

class AuthSettingsController
{
    // Affiche la liste et le formulaire

    protected function checkPermission($permission)
    {
        $app = \App\Core\Application::getInstance();
        $rbacService = $app->make(\Modules\RBAC\Services\RbacService::class);
        $user = null;
        if (function_exists('auth')) {
            $user = auth()->user();
        } elseif (isset($_SESSION['user_id'])) {
            $user = \Modules\Users\Models\User::find($_SESSION['user_id']);
        }
        if (!$user || !$rbacService->userHasPermission($user, $permission)) {
            http_response_code(403); echo 'Accès refusé'; exit;
        }
    }

    public function index()
    {
        $this->checkPermission('admin.auth.settings.view');
        $settings = AuthSetting::all();
        echo view('auth/admin/auth_settings', [
            'settings' => $settings
        ]);
    }

    // Mise à jour d'un paramètre
    public function update()
    {
        $this->checkPermission('admin.auth.settings.edit');
        $fields = [
            'max_login_retries' => 'int',
            'lockout_period' => 'int',
            'max_lockouts' => 'int',
            'password_reset_retries' => 'int',
            'blacklist' => 'json',
        ];
        foreach ($fields as $key => $type) {
            if (isset($_POST[$key])) {
                $value = $_POST[$key];
                if ($type === 'json') {
                    json_decode($value);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $_SESSION['flash_error'] = "Format JSON invalide pour la blacklist.";
                        $settings = AuthSetting::all();
                        echo view('auth/admin/auth_settings', [
                            'settings' => $settings
                        ]);
                        return;
                    }
                }
                $setting = AuthSetting::query()->where('key', $key)->first();
                if ($setting) {
                    AuthSetting::query()->where('key', $key)->update(['value' => $value, 'type' => $type]);
                } else {
                    AuthSetting::create([
                        'key' => $key,
                        'value' => $value,
                        'type' => $type,
                        'description' => '',
                    ]);
                }
            }
        }
        $_SESSION['flash_success'] = 'Paramètres enregistrés.';
        redirect('/admin/auth/settings');
    }

    // Ajout d'un paramètre
    public function add()
    {
        $this->checkPermission('admin.auth.settings.add');
        $data = [
            'key' => $_POST['key'] ?? '',
            'value' => $_POST['value'] ?? '',
            'type' => $_POST['type'] ?? 'string',
            'description' => $_POST['description'] ?? '',
        ];
        AuthSetting::create($data);
        $_SESSION['flash_success'] = 'Paramètre ajouté.';
        redirect('/admin/auth/settings');
    }
}
