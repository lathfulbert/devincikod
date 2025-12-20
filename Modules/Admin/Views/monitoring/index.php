@extends('backend.layouts.master')

@section('title', $title ?? 'Monitoring')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Monitoring</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= route('admin.dashboard') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item active">Monitoring</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <!-- Alerts -->
    <?php if ($msg = flash('success')): ?>
        <div class="alert alert-success" role="alert">
            <?= e($msg) ?>
        </div>
    <?php endif; ?>
    <?php if ($msg = flash('error')): ?>
        <div class="alert alert-danger" role="alert">
            <?= e($msg) ?>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-sm-6 col-xl-3 col-lg-6">
            <div class="card o-hidden">
                <div class="bg-primary b-r-4 card-body">
                    <div class="media static-top-widget">
                        <div class="align-self-center text-center">
                            <i data-feather="list" style="width: 36px; height: 36px;"></i>
                        </div>
                        <div class="media-body">
                            <span class="m-0">Total Logs</span>
                            <h4 class="mb-0 counter"><?= $stats['total_logs'] ?></h4>
                            <i data-feather="list" class="icon-bg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3 col-lg-6">
            <div class="card o-hidden">
                <div class="bg-danger b-r-4 card-body">
                    <div class="media static-top-widget">
                        <div class="align-self-center text-center">
                            <i data-feather="alert-circle" style="width: 36px; height: 36px;"></i>
                        </div>
                        <div class="media-body">
                            <span class="m-0">Errors</span>
                            <h4 class="mb-0 counter"><?= $stats['errors'] ?></h4>
                            <i data-feather="alert-circle" class="icon-bg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3 col-lg-6">
            <div class="card o-hidden">
                <div class="bg-warning b-r-4 card-body">
                    <div class="media static-top-widget">
                        <div class="align-self-center text-center">
                            <i data-feather="alert-triangle" style="width: 36px; height: 36px;"></i>
                        </div>
                        <div class="media-body">
                            <span class="m-0">Warnings</span>
                            <h4 class="mb-0 counter"><?= $stats['warnings'] ?></h4>
                            <i data-feather="alert-triangle" class="icon-bg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3 col-lg-6">
            <div class="card o-hidden">
                <div class="bg-success b-r-4 card-body">
                    <div class="media static-top-widget">
                        <div class="align-self-center text-center">
                            <i data-feather="clock" style="width: 36px; height: 36px;"></i>
                        </div>
                        <div class="media-body">
                            <span class="m-0">Today</span>
                            <h4 class="mb-0 counter"><?= $stats['today'] ?></h4>
                            <i data-feather="clock" class="icon-bg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="<?= url('/admin/monitoring') ?>" class="row g-3">
                        <div class="col-md-3">
                            <label for="filterLevel" class="form-label">Niveau</label>
                            <select name="level" id="filterLevel" class="form-select" onchange="this.form.submit()">
                                <option value="">Tous les niveaux</option>
                                <?php foreach ($levels ?? [] as $level): ?>
                                    <option value="<?= $level ?>" <?= $filterLevel === $level ? 'selected' : '' ?>>
                                        <?= $level ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterChannel" class="form-label">Channel</label>
                            <select name="channel" id="filterChannel" class="form-select" onchange="this.form.submit()">
                                <option value="">Tous les channels</option>
                                <?php foreach ($channels ?? [] as $channel): ?>
                                    <option value="<?= $channel ?>" <?= $filterChannel === $channel ? 'selected' : '' ?>>
                                        <?= $channel ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterDate" class="form-label">Date</label>
                            <input type="date" name="date" id="filterDate" class="form-control"
                                value="<?= $filterDate ?? '' ?>" onchange="this.form.submit()">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label d-block">&nbsp;</label>
                            <?php if ($filterLevel || $filterChannel || $filterDate): ?>
                                <a href="<?= url('/admin/monitoring') ?>" class="btn btn-secondary">
                                    <i data-feather="x"></i> Réinitialiser
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="row mb-3">
        <div class="col-12 text-end">
            <form action="<?= url('/admin/monitoring/clear') ?>" method="POST" onsubmit="return confirm('Are you sure you want to clear all logs?');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-danger">
                    <i data-feather="trash-2"></i> Clear All Logs
                </button>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 class="card-title mb-0">Recent Logs</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Level</th>
                                    <th>Channel</th>
                                    <th>Message</th>
                                    <th>Context</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i data-feather="inbox" class="mb-2"></i><br>
                                            No logs found.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($logs as $log): ?>
                                        <?php
                                        $badgeClass = match (strtolower($log->level)) {
                                            'emergency', 'alert', 'critical', 'error' => 'bg-danger',
                                            'warning' => 'bg-warning text-dark',
                                            'notice' => 'bg-info text-dark',
                                            'info' => 'bg-primary',
                                            'debug' => 'bg-secondary',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <tr>
                                            <td><span class="badge <?= $badgeClass ?>"><?= strtoupper($log->level) ?></span></td>
                                            <td><?= htmlspecialchars($log->channel) ?></td>
                                            <td style="max-width: 400px;">
                                                <div class="text-truncate" title="<?= htmlspecialchars($log->message) ?>"><?= htmlspecialchars($log->message) ?></div>
                                            </td>
                                            <td>
                                                <?php if ($log->context && $log->context !== '[]'): ?>
                                                    <button type="button" class="btn btn-xs btn-outline-secondary" onclick="showContext(this)" data-context="<?= htmlspecialchars($log->context) ?>">View Data</button>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-nowrap"><?= date('Y-m-d H:i:s', strtotime($log->created_at)) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($totalPages > 1): ?>
                        <div class="mt-3">
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mb-0">
                                    <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $currentPage - 1 ?>">Previous</a>
                                    </li>
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $currentPage + 1 ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Context Modal -->
<div class="modal fade" id="contextModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Log Context Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <pre id="contextContent" class="bg-light p-3 rounded"></pre>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    feather.replace();

    function showContext(btn) {
        const context = btn.getAttribute('data-context');
        try {
            const data = JSON.parse(context);
            document.getElementById('contextContent').textContent = JSON.stringify(data, null, 2);
            new bootstrap.Modal(document.getElementById('contextModal')).show();
        } catch (e) {
            alert('Invalid JSON data');
        }
    }
</script>
@endsection