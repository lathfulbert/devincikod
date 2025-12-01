<?php

/**
 * Composant Alert
 * Affiche les messages flash (success, danger, warning, info)
 * 
 * Usage: component('alert')
 * Usage avec type spécifique: component('alert', ['type' => 'success'])
 */

$type = $type ?? null;
$dismissible = $dismissible ?? true;

// Types de messages flash à afficher
$messageTypes = $type ? [$type] : ['success', 'danger', 'warning', 'info'];

foreach ($messageTypes as $msgType):
    $message = flash($msgType);
    if ($message):
        $iconMap = [
            'success' => 'bi-check-circle-fill',
            'danger' => 'bi-exclamation-triangle-fill',
            'warning' => 'bi-exclamation-circle-fill',
            'info' => 'bi-info-circle-fill'
        ];
        $icon = $iconMap[$msgType] ?? 'bi-info-circle-fill';
?>
        <div class="alert alert-<?= $msgType ?> <?= $dismissible ? 'alert-dismissible fade show' : '' ?>" role="alert">
            <i class="bi <?= $icon ?> me-2"></i>
            <?= escape($message) ?>
            <?php if ($dismissible): ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <?php endif; ?>
        </div>
<?php
    endif;
endforeach;
?>