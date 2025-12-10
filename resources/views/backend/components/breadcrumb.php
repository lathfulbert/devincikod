<?php

/**
 * Component: Breadcrumb
 * Affiche un fil d'ariane
 * 
 * Usage:
 *   $breadcrumb = [
 *       ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
 *       ['label' => 'Users', 'url' => '/admin/users'],
 *       ['label' => 'Edit User'] // Dernier élément sans URL = actif
 *   ];
 *   include __DIR__ . '/components/breadcrumb.php';
 */

$breadcrumb = $breadcrumb ?? [];
?>

    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $breadcrumb[count($breadcrumb) - 1]['label'] ?? 'Page' ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= url('/admin/dashboard') ?>">
                            <i data-feather="home"></i>
                        </a>
                    </li>
                    <?php foreach ($breadcrumb as $index => $item): ?>
                        <?php if (isset($item['url'])): ?>
                            <li class="breadcrumb-item">
                                <a href="<?= url($item['url']) ?>"><?= htmlspecialchars($item['label']) ?></a>
                            </li>
                        <?php else: ?>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($item['label']) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>
    </div>



