<?php

/** @var \App\Core\Database\Pagination\LengthAwarePaginator $paginator */

if (!$paginator->hasPages()) {
    return;
}
?>

<nav aria-label="Pagination">
    <ul class="pagination justify-content-center">
        <!-- Previous Page Link -->
        <?php if (!$paginator->onFirstPage()): ?>
            <li class="page-item">
                <a class="page-link" href="<?= $paginator->previousPageUrl() ?>" rel="prev">
                    &laquo; Précédent
                </a>
            </li>
        <?php else: ?>
            <li class="page-item disabled">
                <span class="page-link">&laquo; Précédent</span>
            </li>
        <?php endif; ?>

        <!-- Next Page Link -->
        <?php if ($paginator->hasMorePages()): ?>
            <li class="page-item">
                <a class="page-link" href="<?= $paginator->nextPageUrl() ?>" rel="next">
                    Suivant &raquo;
                </a>
            </li>
        <?php else: ?>
            <li class="page-item disabled">
                <span class="page-link">Suivant &raquo;</span>
            </li>
        <?php endif; ?>
    </ul>
</nav>