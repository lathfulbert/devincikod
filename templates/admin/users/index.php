@extends('admin.layout')

@section('content')

<?php
// Breadcrumb
$breadcrumb = [
    ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
    ['label' => 'Utilisateurs']
];
component('breadcrumb');
?>

<!-- Messages Flash -->
<?php component('alerts'); ?>

<!-- Section: Actions rapides -->
<div class="row mb-3">
    <div class="col-12 text-end">
        <a href="<?= url('/admin/users/create') ?>" class="btn btn-primary">
            <i data-feather="plus"></i> Nouvel utilisateur
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php
        $card_title = "Liste des Utilisateurs";
        component('card-start');
        ?>

        <table id="usersTable" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom d'utilisateur</th>
                    <th>Rôles</th>
                    <th>Date de création</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user->id ?></td>
                            <td>
                                <strong><?= htmlspecialchars($user->username ?? '') ?></strong>
                            </td>
                            <td>
                                <?php
                                // Use eager loaded roles (no N+1 problem!)
                                $roles = $user->roles ?? [];
                                if (!empty($roles)) {
                                    foreach ($roles as $role) {
                                        echo '<span class="badge bg-primary me-1">' . htmlspecialchars($role->name) . '</span>';
                                    }
                                } else {
                                    echo '<span class="text-muted">Aucun rôle</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php if (isset($user->created_at)): ?>
                                    <?= date('d/m/Y H:i', strtotime($user->created_at)) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= url('/admin/users/' . $user->id . '/edit') ?>"
                                    class="btn btn-sm btn-warning"
                                    title="Éditer">
                                    <i data-feather="edit" style="width: 14px; height: 14px;"></i>
                                </a>
                                <form action="<?= url('/admin/users/' . $user->id . '/delete') ?>"
                                    method="POST"
                                    style="display:inline;"
                                    onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                    <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                        <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            <i data-feather="inbox"></i> Aucun utilisateur trouvé
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php
        // Card footer with pagination info
        $card_footer = "Total : " . ($users->total() ?? 0) . " utilisateur(s)";
        component('card-end');
        ?>

        <!-- Pagination Links -->
        <div class="mt-4">
            <?= $users->links() ?>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Réinitialiser les icônes Feather
    feather.replace();
</script>
@endsection