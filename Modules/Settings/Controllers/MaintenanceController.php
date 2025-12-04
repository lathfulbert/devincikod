<?php

namespace Modules\Settings\Controllers;

use App\Core\Application;
use Modules\Settings\Models\MaintenanceMode;
use Modules\RBAC\Models\Role;
use Modules\Users\Models\User;

class MaintenanceController
{
    /**
     * Display maintenance mode configuration
     */
    public function index()
    {
        $app = Application::getInstance();

        $config = MaintenanceMode::getCurrent();
        $roles = Role::all();
        $users = User::all();

        echo view('settings/maintenance/index', [
            'title' => 'Mode Maintenance',
            'config' => $config,
            'roles' => $roles,
            'users' => $users
        ]);
    }

    /**
     * Update maintenance configuration
     */
    public function update()
    {
        $config = MaintenanceMode::getCurrent();

        if (!$config) {
            $_SESSION['flash_error'] = 'Configuration introuvable.';
            redirect('/admin/maintenance');
            return;
        }

        // Handle file upload for background image
        $backgroundImage = $config->background_image;

        if (isset($_FILES['background_image']) && $_FILES['background_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../../public/uploads/maintenance/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $extension = pathinfo($_FILES['background_image']['name'], PATHINFO_EXTENSION);
            $filename = 'maintenance_' . time() . '.' . $extension;
            $uploadPath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['background_image']['tmp_name'], $uploadPath)) {
                $backgroundImage = '/uploads/maintenance/' . $filename;

                // Delete old image if exists
                if ($config->background_image && file_exists(__DIR__ . '/../../../public' . $config->background_image)) {
                    unlink(__DIR__ . '/../../../public' . $config->background_image);
                }
            }
        }

        // Parse allowed IPs (comma or newline separated)
        $allowedIps = [];
        if (!empty($_POST['allowed_ips'])) {
            $ips = preg_split('/[\r\n,]+/', $_POST['allowed_ips']);
            $allowedIps = array_filter(array_map('trim', $ips));
        }

        // Parse allowed roles
        $allowedRoles = [];
        if (!empty($_POST['allowed_roles'])) {
            $allowedRoles = array_map('intval', $_POST['allowed_roles']);
        }

        // Parse allowed users
        $allowedUsers = [];
        if (!empty($_POST['allowed_users'])) {
            $allowedUsers = array_map('intval', $_POST['allowed_users']);
        }

        $data = [
            'title' => $_POST['title'] ?? $config->title,
            'message' => $_POST['message'] ?? $config->message,
            'background_image' => $backgroundImage,
            'background_color' => $_POST['background_color'] ?? $config->background_color,
            'start_time' => !empty($_POST['start_time']) ? $_POST['start_time'] : null,
            'end_time' => !empty($_POST['end_time']) ? $_POST['end_time'] : null,
            'allowed_ips' => json_encode($allowedIps),
            'allowed_roles' => json_encode($allowedRoles),
            'allowed_users' => json_encode($allowedUsers),
            'show_countdown' => isset($_POST['show_countdown']) ? 1 : 0,
            'retry_after' => (int)($_POST['retry_after'] ?? 3600)
        ];

        if ($config->update($data)) {
            $_SESSION['flash_success'] = 'Configuration mise à jour avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la mise à jour.';
        }

        redirect('/admin/maintenance');
    }

    /**
     * Toggle maintenance mode on/off
     */
    public function toggle()
    {
        $config = MaintenanceMode::getCurrent();

        if (!$config) {
            $_SESSION['flash_error'] = 'Configuration introuvable.';
            redirect('/admin/maintenance');
            return;
        }

        $newStatus = !$config->is_enabled ? 1 : 0;
        $config->update(['is_enabled' => $newStatus]);

        if ($newStatus) {
            $_SESSION['flash_success'] = '🔧 Mode maintenance ACTIVÉ. Le site est maintenant en maintenance.';
        } else {
            $_SESSION['flash_success'] = '✅ Mode maintenance DÉSACTIVÉ. Le site est de nouveau accessible.';
        }

        redirect('/admin/maintenance');
    }

    /**
     * Remove background image
     */
    public function removeBackgroundImage()
    {
        $config = MaintenanceMode::getCurrent();

        if (!$config) {
            $_SESSION['flash_error'] = 'Configuration introuvable.';
            redirect('/admin/maintenance');
            return;
        }

        // Delete file if exists
        if ($config->background_image && file_exists(__DIR__ . '/../../../public' . $config->background_image)) {
            unlink(__DIR__ . '/../../../public' . $config->background_image);
        }

        $config->update(['background_image' => null]);

        $_SESSION['flash_success'] = 'Image de fond supprimée.';
        redirect('/admin/maintenance');
    }
}
