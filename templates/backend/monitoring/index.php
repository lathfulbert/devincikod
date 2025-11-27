@extends('backend.layouts.master')

@section('title', $title ?? 'Monitoring')

@section('content')
@if ($msg = flash('success'))
<div class="alert alert-success" role="alert">
    {{ e($msg) }}
</div>
@endif
@if ($msg = flash('error'))
<div class="alert alert-danger" role="alert">
    {{ e($msg) }}
</div>
@endif
<div class="card-body">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h6 class="card-title mb-0">Total Logs</h6>
            <h2 class="my-2"><?= $stats['total_logs'] ?></h2>
            <small>All time</small>
        </div>
        <i data-feather="list" class="feather-32"></i>
    </div>
</div>
</div>
</div>
<div class="col-md-3">
    <div class="card text-white bg-danger h-100">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title mb-0">Errors</h6>
                    <h2 class="my-2"><?= $stats['errors'] ?></h2>
                    <small>Critical issues</small>
                </div>
                <i data-feather="alert-circle" class="feather-32"></i>
            </div>
        </div>
    </div>
</div>
<div class="col-md-3">
    <div class="card text-white bg-warning h-100">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title mb-0">Warnings</h6>
                    <h2 class="my-2"><?= $stats['warnings'] ?></h2>
                    <small>Potential issues</small>
                </div>
                <i data-feather="alert-triangle" class="feather-32"></i>
            </div>
        </div>
    </div>
</div>
<div class="col-md-3">
    <div class="card text-white bg-success h-100">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title mb-0">Today</h6>
                    <h2 class="my-2"><?= $stats['today'] ?></h2>
                    <small>Logs generated today</small>
                </div>
                <i data-feather="clock" class="feather-32"></i>
            </div>
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
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Recent Logs</h5>
    </div>
    <div class="card-body p-0">
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
    </div>
    <?php if ($totalPages > 1): ?>
        <div class="card-footer">
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