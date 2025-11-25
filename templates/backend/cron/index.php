@extends('backend.layouts.master')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Cron Tasks</h3>
            </div>
            <div class="col-6">
                <?php
                // Breadcrumb
                $breadcrumb = [
                    ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
                    ['label' => 'Cron Tasks']
                ];
                component('breadcrumb');
                ?>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <!-- Messages Flash -->
    <?php component('alerts'); ?>
</div>

<div class="container-fluid">
    <!-- Stats Row -->
    <div class="row">
        <div class="col-md-4">
            <div class="card o-hidden">
                <div class="bg-primary b-r-4 card-body">
                    <div class="media static-top-widget">
                        <div class="align-self-center text-center">
                            <i data-feather="clock" style="width: 48px; height: 48px;"></i>
                        </div>
                        <div class="media-body">
                            <span class="m-0">Total Tasks</span>
                            <h4 class="mb-0 counter"><?= $stats['total_tasks'] ?></h4>
                            <i data-feather="clock" class="icon-bg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card o-hidden">
                <div class="bg-success b-r-4 card-body">
                    <div class="media static-top-widget">
                        <div class="align-self-center text-center">
                            <i data-feather="check-circle" style="width: 48px; height: 48px;"></i>
                        </div>
                        <div class="media-body">
                            <span class="m-0">Executions (24h)</span>
                            <h4 class="mb-0 counter"><?= $stats['executions_24h'] ?></h4>
                            <i data-feather="check-circle" class="icon-bg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card o-hidden">
                <div class="bg-info b-r-4 card-body">
                    <div class="media static-top-widget">
                        <div class="align-self-center text-center">
                            <i data-feather="percent" style="width: 48px; height: 48px;"></i>
                        </div>
                        <div class="media-body">
                            <span class="m-0">Success Rate</span>
                            <h4 class="mb-0"><?= $stats['success_rate'] ?>%</h4>
                            <i data-feather="percent" class="icon-bg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5>Registered Cron Tasks</h5>
                            <span class="f-w-500 f-12 f-light mt-0">Manage your scheduled tasks</span>
                        </div>
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="<?= url('/admin/cron/logs') ?>" class="btn btn-outline-info">
                                <i data-feather="activity"></i> View Logs
                            </a>
                            <a href="<?= url('/admin/cron/stats') ?>" class="btn btn-outline-primary">
                                <i data-feather="bar-chart-2"></i> Statistics
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordernone">
                            <thead>
                                <tr class="border-bottom-primary">
                                    <th scope="col">Task Name</th>
                                    <th scope="col">Expression</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Last Run</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tasks as $task): ?>
                                    <tr>
                                        <td><code class="text-dark"><?= htmlspecialchars($task['name']) ?></code></td>
                                        <td><span class="badge badge-light-secondary"><?= htmlspecialchars($task['expression']) ?></span></td>
                                        <td><span class="f-light"><?= htmlspecialchars($task['description']) ?></span></td>
                                        <td>
                                            <?php if ($task['last_run']): ?>
                                                <span class="f-light"><?= date('d/m/Y H:i', strtotime($task['last_run'])) ?></span>
                                                <?php if ($task['last_status'] === 'success'): ?>
                                                    <i data-feather="check-circle" class="text-success" style="width: 14px; height: 14px;"></i>
                                                <?php else: ?>
                                                    <i data-feather="alert-circle" class="text-danger" style="width: 14px; height: 14px;"></i>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="f-light">Never</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($task['enabled']): ?>
                                                <span class="badge badge-light-success">
                                                    <i data-feather="check" style="width: 12px; height: 12px;"></i> Enabled
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-light-danger">
                                                    <i data-feather="x" style="width: 12px; height: 12px;"></i> Disabled
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <form method="POST" action="<?= url('/admin/cron/run') ?>" style="display:inline;">
                                                    <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
                                                    <input type="hidden" name="task_class" value="<?= htmlspecialchars($task['class']) ?>">
                                                    <button type="submit" class="btn btn-primary" title="Run Now">
                                                        <i data-feather="play" style="width: 14px; height: 14px;"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" action="<?= url('/admin/cron/toggle') ?>" style="display:inline;">
                                                    <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
                                                    <input type="hidden" name="task_class" value="<?= htmlspecialchars($task['class']) ?>">
                                                    <button type="submit" class="btn btn-<?= $task['enabled'] ? 'warning' : 'success' ?>"
                                                        title="<?= $task['enabled'] ? 'Disable' : 'Enable' ?>">
                                                        <i data-feather="<?= $task['enabled'] ? 'pause' : 'play' ?>" style="width: 14px; height: 14px;"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
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