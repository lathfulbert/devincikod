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




    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-6">
                       <?php if ($title): ?>
                        <h3><?= htmlspecialchars($title) ?></h3>
                    <?php endif; ?>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.default_dashboard') }}">
                                <svg class="stroke-icon">
                                    <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                                </svg></a></li>
                        <li class="breadcrumb-item"><?= $title ?></li>
                            <?php if ($actions): ?>

                        <li class="breadcrumb-item active"><?= $title ?> </li>
                    <?php endif; ?>
                        </ol>
                </div>
            </div>
        </div>
    </div>

