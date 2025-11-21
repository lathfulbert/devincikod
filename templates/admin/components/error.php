<?php

/**
 * Composant Error
 * Affiche l'erreur de validation d'un champ
 * 
 * Usage: component('error', ['field' => 'email'])
 */

$field = $field ?? '';
$errorMessage = error($field);
?>

<?php if ($errorMessage): ?>
    <div class="invalid-feedback d-block">
        <?= escape($errorMessage) ?>
    </div>
<?php endif; ?>