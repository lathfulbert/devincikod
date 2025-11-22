<?php

/**
 * Component: Alerts
 * Affiche des messages flash stylisés
 * 
 * Usage:
 *   include __DIR__ . '/components/alerts.php';
 */

if (isset($_SESSION['flash'])):
    foreach ($_SESSION['flash'] as $type => $messages):
        if (!is_array($messages)) {
            $messages = [$messages];
        }

        // Déterminer la classe d'icône selon le type
        $icons = [
            'success' => 'check-circle',
            'danger' => 'x-circle',
            'warning' => 'alert-triangle',
            'info' => 'info'
        ];
        $icon = $icons[$type] ?? 'info';

        foreach ($messages as $message):
?>
            <div class="alert alert-<?= $type ?> alert-dismissible fade show" role="alert">
                <i data-feather="<?= $icon ?>"></i>
                <strong><?= ucfirst($type) ?>!</strong> <?= htmlspecialchars($message) ?>
                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
<?php
        endforeach;
    endforeach;

    // Nettoyer les messages flash après les avoir affichés
    unset($_SESSION['flash']);
endif;
?>