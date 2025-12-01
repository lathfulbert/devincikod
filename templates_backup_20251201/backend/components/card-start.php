<?php

/**
 * Component: Card
 * Conteneur de contenu avec en-tête et pieds optionnels
 * 
 * Usage:
 *   $card_title = "Mon Titre";
 *   $card_actions = '<button class="btn btn-primary">Action</button>';
 *   $card_footer = "Pied de page";
 *   ob_start();
 */

$title = $card_title ?? '';
$actions = $card_actions ?? '';
$footer = $card_footer ?? false;
?>

<div class="card">
    <?php if ($title || $actions): ?>
        <div class="card-header pb-0">
            <div class="row">
                <div class="col">
                    <?php if ($title): ?>
                        <h5><?= htmlspecialchars($title) ?></h5>
                    <?php endif; ?>
                </div>
                <?php if ($actions): ?>
                    <div class="col text-end">
                        <?= $actions ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="card-body">
        <?php
        // Le contenu doit être placé après l'inclusion de ce fichier
        // Ou passé via $card_content
        if (isset($card_content)) {
            echo $card_content;
        }
        ?>