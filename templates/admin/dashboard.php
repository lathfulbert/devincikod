@extends('admin.layout')

@section('styles')
<style>
    .stat-card {
        border-radius: 10px;
        padding: 20px;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 150px;
        height: 150px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .stat-card h3 {
        font-size: 2.5rem;
        font-weight: bold;
        margin: 0;
    }

    .stat-card p {
        margin: 5px 0 0 0;
        opacity: 0.9;
    }

    .stat-icon {
        position: absolute;
        top: 20px;
        right: 20px;
        opacity: 0.3;
    }
</style>
@endsection

@section('content')

<?php
// Breadcrumb
$breadcrumb = [
    ['label' => 'Dashboard']
];
component('breadcrumb');
?>

<!-- Messages Flash -->
<?php component('alerts'); ?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <!-- Users Card -->
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <i data-feather="users" class="stat-icon" style="width: 60px; height: 60px;"></i>
            <h3><?= $stats['users_count'] ?? 0 ?></h3>
            <p>Utilisateurs</p>
            <a href="<?= url('/admin/users') ?>" class="text-white" style="font-size: 0.9rem; text-decoration: none;">
                Voir tout <i data-feather="arrow-right" style="width: 14px; height: 14px;"></i>
            </a>
        </div>
    </div>

    <!-- Roles Card -->
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <i data-feather="shield" class="stat-icon" style="width: 60px; height: 60px;"></i>
            <h3><?= $stats['roles_count'] ?? 0 ?></h3>
            <p>Rôles</p>
            <a href="<?= url('/admin/roles') ?>" class="text-white" style="font-size: 0.9rem; text-decoration: none;">
                Voir tout <i data-feather="arrow-right" style="width: 14px; height: 14px;"></i>
            </a>
        </div>
    </div>

    <!-- Permissions Card -->
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <i data-feather="key" class="stat-icon" style="width: 60px; height: 60px;"></i>
            <h3><?= $stats['permissions_count'] ?? 0 ?></h3>
            <p>Permissions</p>
            <a href="<?= url('/admin/permissions') ?>" class="text-white" style="font-size: 0.9rem; text-decoration: none;">
                Voir tout <i data-feather="arrow-right" style="width: 14px; height: 14px;"></i>
            </a>
        </div>
    </div>

    <!-- Modules Card -->
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <i data-feather="package" class="stat-icon" style="width: 60px; height: 60px;"></i>
            <h3><?= $stats['modules_count'] ?? 0 ?></h3>
            <p>Modules</p>
            <a href="<?= url('/admin/modules') ?>" class="text-white" style="font-size: 0.9rem; text-decoration: none;">
                Voir tout <i data-feather="arrow-right" style="width: 14px; height: 14px;"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Welcome & Quick Actions -->
    <div class="col-lg-8 mb-4">
        <?php
        $card_title = "Bienvenue sur SunuFramework Admin";
        component('card-start');
        ?>

        <div class="mb-4">
            <h5><i data-feather="zap"></i> Tableau de bord</h5>
            <p class="text-muted">Gérez votre application depuis ce panneau d'administration. Utilisez la sidebar pour naviguer entre les différents modules.</p>
        </div>

        <h6 class="mb-3"><i data-feather="activity"></i> Actions rapides</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="d-grid">
                    <a href="<?= url('/admin/users/create') ?>" class="btn btn-outline-primary">
                        <i data-feather="user-plus"></i> Nouvel utilisateur
                    </a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-grid">
                    <a href="<?= url('/admin/roles/create') ?>" class="btn btn-outline-secondary">
                        <i data-feather="shield"></i> Nouveau rôle
                    </a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-grid">
                    <a href="<?= url('/admin/permissions/create') ?>" class="btn btn-outline-info">
                        <i data-feather="key"></i> Nouvelle permission
                    </a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-grid">
                    <a href="<?= url('/admin/cache') ?>" class="btn btn-outline-success">
                        <i data-feather="settings"></i> Configuration Cache
                    </a>
                </div>
            </div>
        </div>

        <?php component('card-end'); ?>
    </div>

    <!-- Recent Users & System Info -->
    <div class="col-lg-4 mb-4">
        <?php
        $card_title = "Utilisateurs récents";
        component('card-start');
        ?>

        <?php if (!empty($recent_users)): ?>
            <div class="list-group list-group-flush">
                <?php foreach ($recent_users as $user): ?>
                    <div class="list-group-item d-flex align-items-center px-0">
                        <div class="avatar me-3" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                            <?= strtoupper(substr($user->username ?? 'U', 0, 1)) ?>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0"><?= htmlspecialchars($user->username ?? '') ?></h6>
                            <small class="text-muted">
                                <?php if (isset($user->created_at)): ?>
                                    <?= date('d/m/Y H:i', strtotime($user->created_at)) ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </small>
                        </div>
                        <a href="<?= url('/admin/users/' . $user->id . '/edit') ?>" class="btn btn-sm btn-light">
                            <i data-feather="edit" style="width: 14px; height: 14px;"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted text-center my-3">
                <i data-feather="inbox"></i><br>
                Aucun utilisateur récent
            </p>
        <?php endif; ?>

        <?php component('card-end'); ?>

        <!-- System Info -->
        <?php
        $card_title = "Informations Système";
        component('card-start');
        ?>

        <div class="list-group list-group-flush">
            <div class="list-group-item d-flex justify-content-between px-0">
                <span><i data-feather="server"></i> PHP Version</span>
                <strong><?= phpversion() ?></strong>
            </div>
            <div class="list-group-item d-flex justify-content-between px-0">
                <span><i data-feather="database"></i> Base de données</span>
                <strong>MySQL</strong>
            </div>
            <div class="list-group-item d-flex justify-content-between px-0">
                <span><i data-feather="layers"></i> Framework</span>
                <strong>SunuFramework 2.0</strong>
            </div>
        </div>

        <?php component('card-end'); ?>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser les icônes Feather
        feather.replace();
    });
</script>
@endsection