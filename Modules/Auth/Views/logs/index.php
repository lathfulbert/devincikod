@extends('backend.layouts.master')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Journal d'authentification</h3>
                </div>
                <div class="card-body">
                    <?php component('alerts'); ?>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Utilisateur</th>
                                    <th>Événement</th>
                                    <th>IP</th>
                                    <th>Détails</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Aucun événement</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td><?= date('Y-m-d H:i:s', strtotime($log->created_at)) ?></td>
                                            <td>
                                                <?php if ($log->user_id): ?>
                                                    <a href="<?= url('/admin/users/' . $log->user_id) ?>">
                                                        Utilisateur #<?= $log->user_id ?>
                                                    </a>
                                                <?php else: ?>
                                                    <em>N/A</em>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $badgeClass = 'secondary';
                                                if (strpos($log->event_type, 'success') !== false) {
                                                    $badgeClass = 'success';
                                                } elseif (strpos($log->event_type, 'failed') !== false) {
                                                    $badgeClass = 'danger';
                                                } elseif (strpos($log->event_type, 'locked') !== false) {
                                                    $badgeClass = 'warning';
                                                }
                                                ?>
                                                <span class="badge bg-<?= $badgeClass ?>">
                                                    <?= $log->event_type ?>
                                                </span>
                                            </td>
                                            <td><?= $log->ip_address ?? 'N/A' ?></td>
                                            <td>
                                                <?php if ($log->details): ?>
                                                    <button class="btn btn-sm btn-info" onclick="showDetails(<?= htmlspecialchars($log->details) ?>)">
                                                        Voir détails
                                                    </button>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($totalPages > 1): ?>
                        <nav>
                            <ul class="pagination">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function showDetails(details) {
        alert('Détails:\n' + JSON.stringify(JSON.parse(details), null, 2));
    }
</script>
@endsection