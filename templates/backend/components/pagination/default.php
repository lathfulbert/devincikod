<?php

/** @var \App\Core\Database\Pagination\LengthAwarePaginator $paginator */

if (!$paginator->hasPages()) {
    return;
}

$currentPage = $paginator->currentPage();
$lastPage = $paginator->lastPage();
$onEachSide = 2; // Number of pages on each side of current page
?>

<nav aria-label="Pagination">
    <ul class="pagination justify-content-center">
        <!-- Previous Page Link -->
        <?php if (!$paginator->onFirstPage()): ?>
            <li class="page-item">
                <a class="page-link" href="<?= $paginator->previousPageUrl() ?>" rel="prev" aria-label="Précédent">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
        <?php else: ?>
            <li class="page-item disabled" aria-disabled="true">
                <span class="page-link" aria-hidden="true">&laquo;</span>
            </li>
        <?php endif; ?>

        <!-- Pagination Elements -->
        <?php
        $start = max(1, $currentPage - $onEachSide);
        $end = min($lastPage, $currentPage + $onEachSide);

        // Always show first page
        if ($start > 1) :
        ?>
            <li class="page-item">
                <a class="page-link" href="<?= $paginator->url(1) ?>">1</a>
            </li>
            <?php if ($start > 2): ?>
                <li class="page-item disabled"><span class="page-link">...</span></li>
            <?php endif; ?>
        <?php
        endif;

        // Page Numbers
        for ($page = $start; $page <= $end; $page++):
        ?>
            <?php if ($page == $currentPage): ?>
                <li class="page-item active" aria-current="page">
                    <span class="page-link"><?= $page ?></span>
                </li>
            <?php else: ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $paginator->url($page) ?>"><?= $page ?></a>
                </li>
            <?php endif; ?>
        <?php endfor; ?>

        <!-- Always show last page -->
        <?php if ($end < $lastPage): ?>
            <?php if ($end < $lastPage - 1): ?>
                <li class="page-item disabled"><span class="page-link">...</span></li>
            <?php endif; ?>
            <li class="page-item">
                <a class="page-link" href="<?= $paginator->url($lastPage) ?>"><?= $lastPage ?></a>
            </li>
        <?php endif; ?>

        <!-- Next Page Link -->
        <?php if ($paginator->hasMorePages()): ?>
            <li class="page-item">
                <a class="page-link" href="<?= $paginator->nextPageUrl() ?>" rel="next" aria-label="Suivant">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        <?php else: ?>
            <li class="page-item disabled" aria-disabled="true">
                <span class="page-link" aria-hidden="true">&raquo;</span>
            </li>
        <?php endif; ?>
    </ul>
</nav>

<!-- Items Info -->
<div class="d-flex justify-content-center align-items-center mt-2">
    <small class="text-muted">
        Affichage de <?= $paginator->firstItem() ?> à <?= $paginator->lastItem() ?> sur <?= $paginator->total() ?> résultats
    </small>
</div>