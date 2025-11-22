<?php

/**
 * Template CRUD: Liste (Index)
 * 
 * Ce template est un exemple générique de page de liste avec DataTable.
 * Adaptez les variables selon votre module.
 * 
 * Variables requises:
 * - $items: array - Liste des éléments à afficher
 * - $module_name: string - Nom du module (ex: 'users')
 * - $module_title: string - Titre du module (ex: 'Utilisateurs')
 * - $columns: array - Définition des colonnes
 */
?>
@extends('admin.layout')

@section('content')

<?php
// Breadcrumb
$breadcrumb = [
    ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
    ['label' => $module_title ?? 'Module']
];
component('breadcrumb');
?>

<!-- Messages Flash -->
<?php component('alerts'); ?>

<!-- Section: Actions rapides -->
<div class="row mb-3">
    <div class="col-12 text-end">
        <a href="<?= url('/admin/' . ($module_name ?? 'module') . '/create') ?>" class="btn btn-primary">
            <i data-feather="plus"></i> Nouveau
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php
        $card_title = "Liste des " . ($module_title ?? 'Éléments');
        $card_actions = '';
        component('card-start');
        ?>

        <table id="dataTable" class="table table-striped table-hover">
            <thead>
                <tr>
                    <?php foreach ($columns ?? [] as $column): ?>
                        <th><?= htmlspecialchars($column['label']) ?></th>
                    <?php endforeach; ?>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <?php foreach ($columns ?? [] as $column): ?>
                                <td>
                                    <?php
                                    $value = $item->{$column['field']} ?? '';

                                    // Type de rendu selon configuration
                                    if (isset($column['type'])) {
                                        switch ($column['type']) {
                                            case 'badge':
                                                $badgeClass = $column['badge_class'] ?? 'bg-primary';
                                                echo '<span class="badge ' . $badgeClass . '">' . htmlspecialchars($value) . '</span>';
                                                break;
                                            case 'boolean':
                                                echo $value ? '<span class="badge bg-success">Oui</span>' : '<span class="badge bg-secondary">Non</span>';
                                                break;
                                            case 'date':
                                                echo date('d/m/Y H:i', strtotime($value));
                                                break;
                                            default:
                                                echo htmlspecialchars($value);
                                        }
                                    } else {
                                        echo htmlspecialchars($value);
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                            <td class="text-end">
                                <a href="<?= url('/admin/' . ($module_name ?? 'module') . '/edit/' . $item->id) ?>"
                                    class="btn btn-sm btn-warning" title="Éditer">
                                    <i data-feather="edit" style="width: 14px; height: 14px;"></i>
                                </a>
                                <a href="<?= url('/admin/' . ($module_name ?? 'module') . '/delete/' . $item->id) ?>"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')"
                                    title="Supprimer">
                                    <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?= count($columns ?? []) + 1 ?>" class="text-center text-muted">
                            <i data-feather="inbox"></i> Aucun élément trouvé
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php
        $card_footer = "Total : " . count($items ?? []) . " élément(s)";
        component('card-end');
        ?>
    </div>
</div>

@endsection

@section('scripts')
<?php
$datatable_id = 'dataTable';
$datatable_config = [
    'order' => [[0, 'asc']],
    'pageLength' => 10,
    'responsive' => true
];
component('datatable-init');
?>
<script>
    // Réinitialiser les icônes Feather après le rendu DataTable
    feather.replace();
</script>
@endsection