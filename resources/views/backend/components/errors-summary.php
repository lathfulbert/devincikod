<?php

/**
 * Composant Errors Summary
 * Affiche un résumé de toutes les erreurs de validation
 * 
 * Usage: component('errors-summary')
 */

$errorBag = errors();
?>

<?php if ($errorBag->any()): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Erreur de validation</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

        <ul class="mb-0 mt-2">
            <?php foreach ($errorBag->flatten() as $error): ?>
                <li><?= escape($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>