<?php

namespace Modules\Dashboard\Controllers;

use App\Core\Application;
use Modules\RBAC\Models\Module as ModuleModel;

class DashboardController
{
    public function index()
    {
        // Vérification d'accès
        if (!auth()->check()) {
            http_response_code(403);
            echo view('errors.403', [
                'message' => "Vous devez être connecté pour accéder au dashboard.",
                'required_permission' => 'auth'
            ]);
            return;
        }

        $user = auth()->user();
        $isAdmin = false;
        if (is_object($user) && method_exists($user, 'hasRole')) {
            $isAdmin = $user->hasRole('admin');
        } elseif (is_array($user) && isset($user['role']) && $user['role'] === 'admin') {
            $isAdmin = true;
        }

        if (!$isAdmin && (!is_object($user) || !method_exists($user, 'can') || !$user->can('access.dashboard'))) {
            http_response_code(403);
            echo view('errors.403', [
                'message' => "Vous n'avez pas l'autorisation d'accéder au dashboard.",
                'required_permission' => 'access.dashboard'
            ]);
            return;
        }

        // Découvrir et charger tous les widgets de tous les modules
        $registry = \App\Core\Widget\WidgetRegistry::getInstance();
        $registry->discoverAllWidgets();
        $widgets = [];
        $debugWidgetNames = [];
        foreach ($registry->getWidgetsForUser($user) as $widget) {
            $debugWidgetNames[] = get_class($widget);
            $data = $widget->getData();
            ob_start();
            ?>
            <div class="card text-bg-info h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <?php if (!empty($data['icon'])): ?><i data-feather="<?= htmlspecialchars($data['icon']) ?>"></i> <?php endif; ?>
                        <?= htmlspecialchars($data['title'] ?? 'Widget') ?>
                    </h5>
                    <?php if (!empty($data['description'])): ?>
                        <p><?= htmlspecialchars($data['description']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($data['value'])): ?>
                        <div class="display-6 fw-bold mb-0"><?= htmlspecialchars($data['value']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($data['sent']) || !empty($data['failed']) || !empty($data['pending'])): ?>
                        <ul class="list-unstyled mb-0">
                            <?php if (isset($data['sent'])): ?><li><strong>Envoyés :</strong> <?= htmlspecialchars($data['sent']) ?></li><?php endif; ?>
                            <?php if (isset($data['failed'])): ?><li><strong>Échoués :</strong> <?= htmlspecialchars($data['failed']) ?></li><?php endif; ?>
                            <?php if (isset($data['pending'])): ?><li><strong>Campagnes en cours :</strong> <?= htmlspecialchars($data['pending']) ?></li><?php endif; ?>
                        </ul>
                    <?php endif; ?>
                    <?php if (isset($data['credit'])): ?>
                        <p class="display-6 fw-bold mb-0"><?= number_format($data['credit'], 0, ',', ' ') ?> F&nbsp;CFA</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php
            $widgets[] = ob_get_clean();
        }
        $debugAlert = null;
        if (empty($widgets)) {
            $debugAlert = '<div class="alert alert-warning">Aucun widget trouvé. Widgets détectés (debug) : <pre>' . print_r($debugWidgetNames, true) . '</pre></div>';
        }

        // Préparer les données pour la vue
        $data = [
            'title' => 'Dashboard',
            'widgets' => $widgets,
            'user' => $user,
            'is_admin' => $isAdmin,
            'debugAlert' => $debugAlert
        ];

       

        // Forcer la vue du module Dashboard (notation relative comme dans les autres modules)
        echo view('dashboard/index', $data);
    }
}