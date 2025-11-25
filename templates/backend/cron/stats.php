@extends('backend.layouts.master')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Cron Statistics</h3>
            </div>
            <div class="col-6">
                <?php
                // Breadcrumb
                $breadcrumb = [
                    ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
                    ['label' => 'Cron Tasks', 'url' => '/admin/cron'],
                    ['label' => 'Statistics']
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
                    <h5>Cron Task Performance</h5>
                    <span class="f-w-500 f-12 f-light mt-0">Detailed statistics for all tasks</span>
                </div>
                <div class="card-body">
                    <?php if (empty($task_stats)): ?>
                        <div class="alert alert-light-info">
                            <i data-feather="info"></i>
                            <span class="f-light">No execution data available yet. Tasks need to run at least once.</span>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordernone">
                                <thead>
                                    <tr class="border-bottom-primary">
                                        <th scope="col">Task Name</th>
                                        <th scope="col">Total Executions</th>
                                        <th scope="col">Successful</th>
                                        <th scope="col">Failed</th>
                                        <th scope="col">Success Rate</th>
                                        <th scope="col">Last Run</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($task_stats as $stat): ?>
                                        <?php
                                        $successRate = $stat['executions'] > 0
                                            ? round(($stat['successful'] / $stat['executions']) * 100, 1)
                                            : 0;
                                        ?>
                                        <tr>
                                            <td><code class="text-dark"><?= htmlspecialchars(basename(str_replace('\\', '/', $stat['task_class']))) ?></code></td>
                                            <td><span class="badge badge-light-primary"><?= $stat['executions'] ?></span></td>
                                            <td><span class="badge badge-light-success"><?= $stat['successful'] ?></span></td>
                                            <td>
                                                <?php if ($stat['failed'] > 0): ?>
                                                    <span class="badge badge-light-danger"><?= $stat['failed'] ?></span>
                                                <?php else: ?>
                                                    <span class="f-light">0</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 20px; width: 100px;">
                                                        <div class="progress-bar progress-bar-striped <?= $successRate >= 80 ? 'bg-success' : ($successRate >= 50 ? 'bg-warning' : 'bg-danger') ?>"
                                                            style="width: <?= $successRate ?>%">
                                                            <span class="f-12 f-w-600"><?= $successRate ?>%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="f-light">
                                                    <?= $stat['last_run'] ? date('d/m/Y H:i', strtotime($stat['last_run'])) : 'Never' ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Execution Distribution Chart -->
                        <div class="mt-4">
                            <h6 class="f-w-600">Execution Distribution</h6>
                            <?php
                            $maxExecutions = max(array_column($task_stats, 'executions'));
                            foreach ($task_stats as $stat):
                                $barWidth = $maxExecutions > 0 ? ($stat['executions'] / $maxExecutions) * 100 : 0;
                            ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="f-light"><?= htmlspecialchars(basename(str_replace('\\', '/', $stat['task_class']))) ?></small>
                                        <small class="f-light"><?= $stat['executions'] ?> runs</small>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-primary progress-bar-animated" style="width: <?= $barWidth ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <?php if (!empty($task_stats)): ?>
        <div class="row mt-3">
            <div class="col-md-4">
                <div class="card o-hidden">
                    <div class="bg-primary b-r-4 card-body">
                        <div class="media static-top-widget">
                            <div class="align-self-center text-center">
                                <i data-feather="layers" style="width: 36px; height: 36px;"></i>
                            </div>
                            <div class="media-body">
                                <span class="m-0">Tasks with History</span>
                                <h4 class="mb-0"><?= count($task_stats) ?></h4>
                                <i data-feather="layers" class="icon-bg"></i>
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
                                <i data-feather="activity" style="width: 36px; height: 36px;"></i>
                            </div>
                            <div class="media-body">
                                <span class="m-0">Total Executions</span>
                                <h4 class="mb-0"><?= array_sum(array_column($task_stats, 'executions')) ?></h4>
                                <i data-feather="activity" class="icon-bg"></i>
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
                                <i data-feather="trending-up" style="width: 36px; height: 36px;"></i>
                            </div>
                            <div class="media-body">
                                <span class="m-0">Overall Success Rate</span>
                                <?php
                                $totalExec = array_sum(array_column($task_stats, 'executions'));
                                $totalSuccess = array_sum(array_column($task_stats, 'successful'));
                                $overallRate = $totalExec > 0 ? round(($totalSuccess / $totalExec) * 100, 1) : 0;
                                ?>
                                <h4 class="mb-0"><?= $overallRate ?>%</h4>
                                <i data-feather="trending-up" class="icon-bg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <h5>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="btn-group btn-group-pill" role="group">
                            <a href="<?= url('/admin/cron') ?>" class="btn btn-outline-primary">
                                <i data-feather="list"></i> Manage Tasks
                            </a>
                            <a href="<?= url('/admin/cron/logs') ?>" class="btn btn-outline-info">
                                <i data-feather="activity"></i> View Logs
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

@endsection

@section('scripts')
<script>
    feather.replace();
</script>
@endsection