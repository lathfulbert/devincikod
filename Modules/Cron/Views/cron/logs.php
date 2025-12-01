@extends('backend.layouts.master')

@section('title', $title ?? 'Admin')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Cron Execution Logs</h3>
            </div>
            <div class="col-6">
                <?php
                // Breadcrumb
                $breadcrumb = [
                    ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
                    ['label' => 'Cron Tasks', 'url' => '/admin/cron'],
                    ['label' => 'Execution Logs']
                ];
                component('breadcrumb');
                ?>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5>Cron Execution History</h5>
                            <span class="f-w-500 f-12 f-light mt-0">Total: <?= $pagination['total'] ?> execution(s)</span>
                        </div>
                        <?php if ($filter_task): ?>
                            <div>
                                <a href="<?= url('/admin/cron/logs') ?>" class="btn btn-secondary btn-sm">
                                    <i data-feather="x"></i> Clear Filter
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($logs)): ?>
                        <div class="alert alert-light-info">
                            <i data-feather="info"></i>
                            <span class="f-light">No execution logs found.</span>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordernone">
                                <thead>
                                    <tr class="border-bottom-info">
                                        <th scope="col">Task</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Started</th>
                                        <th scope="col">Duration</th>
                                        <th scope="col">Output/Error</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td>
                                                <code class="text-dark"><?= htmlspecialchars(basename(str_replace('\\', '/', $log['task_class']))) ?></code>
                                                <?php if (!$filter_task): ?>
                                                    <br>
                                                    <a href="?task=<?= urlencode($log['task_class']) ?>" class="f-light f-12">
                                                        <i data-feather="filter" style="width: 12px; height: 12px;"></i> Filter
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($log['status'] === 'success'): ?>
                                                    <span class="badge badge-light-success">
                                                        <i data-feather="check" style="width: 12px; height: 12px;"></i> Success
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-light-danger">
                                                        <i data-feather="x" style="width: 12px; height: 12px;"></i> Failed
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="f-light"><?= date('d/m/Y H:i:s', strtotime($log['started_at'])) ?></span></td>
                                            <td>
                                                <?php if (isset($log['duration_ms'])): ?>
                                                    <span class="badge badge-light-secondary"><?= number_format($log['duration_ms']) ?>ms</span>
                                                <?php else: ?>
                                                    <span class="f-light">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($log['status'] === 'failed' && !empty($log['error'])): ?>
                                                    <span class="text-danger f-light" title="<?= htmlspecialchars($log['error']) ?>">
                                                        <i data-feather="alert-triangle" style="width: 14px; height: 14px;"></i>
                                                        <span class="text-truncate d-inline-block" style="max-width: 300px;">
                                                            <?= htmlspecialchars(substr($log['error'], 0, 50)) ?>...
                                                        </span>
                                                    </span>
                                                <?php elseif (!empty($log['output'])): ?>
                                                    <span class="f-light text-truncate d-inline-block" style="max-width: 300px;" title="<?= htmlspecialchars($log['output']) ?>">
                                                        <?= htmlspecialchars(substr($log['output'], 0, 50)) ?>
                                                        <?php if (strlen($log['output']) > 50): ?>...<?php endif; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="f-light">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($pagination['pages'] > 1): ?>
                            <nav aria-label="Page navigation" class="mt-3">
                                <ul class="pagination pagination-primary justify-content-end">
                                    <?php for ($i = 1; $i <= $pagination['pages']; $i++): ?>
                                        <li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?><?= $filter_task ? '&task=' . urlencode($filter_task) : '' ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    feather.replace();
</script>
@endsection