@extends('backend.layouts.master')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Queue Management</h3>
            </div>
            <div class="col-6">
                <?php
                // Breadcrumb
                $breadcrumb = [
                    ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
                    ['label' => 'Queue Management']
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
    <!-- Stats Cards Row - Cuba Style -->
    <div class="row">
        <div class="col-md-6 col-xl-3">
            <div class="card o-hidden">
                <div class="bg-primary b-r-4 card-body">
                    <div class="media static-top-widget">
                        <div class="align-self-center text-center">
                            <i data-feather="list" style="width: 48px; height: 48px;"></i>
                        </div>
                        <div class="media-body">
                            <span class="m-0">Active Jobs</span>
                            <h4 class="mb-0 counter"><?= $stats['active_jobs'] ?></h4>
                            <i data-feather="list" class="icon-bg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card o-hidden">
                <div class="bg-danger b-r-4 card-body">
                    <div class="media static-top-widget">
                        <div class="align-self-center text-center">
                            <i data-feather="alert-circle" style="width: 48px; height: 48px;"></i>
                        </div>
                        <div class="media-body">
                            <span class="m-0">Failed Jobs</span>
                            <h4 class="mb-0 counter"><?= $stats['failed_jobs'] ?></h4>
                            <i data-feather="alert-circle" class="icon-bg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card o-hidden">
                <div class="bg-success b-r-4 card-body">
                    <div class="media static-top-widget">
                        <div class="align-self-center text-center">
                            <i data-feather="check-circle" style="width: 48px; height: 48px;"></i>
                        </div>
                        <div class="media-body">
                            <span class="m-0">Success Rate</span>
                            <h4 class="mb-0"><?= $stats['success_rate'] ?>%</h4>
                            <i data-feather="check-circle" class="icon-bg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card o-hidden">
                <div class="bg-info b-r-4 card-body">
                    <div class="media static-top-widget">
                        <div class="align-self-center text-center">
                            <i data-feather="clock" style="width: 48px; height: 48px;"></i>
                        </div>
                        <div class="media-body">
                            <span class="m-0">Jobs (24h)</span>
                            <h4 class="mb-0 counter"><?= $stats['total_24h'] ?></h4>
                            <i data-feather="clock" class="icon-bg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="btn-group btn-group-pill" role="group">
                        <a href="<?= url('/admin/queue/jobs') ?>" class="btn btn-outline-primary">
                            <i data-feather="list"></i> View Active Jobs
                        </a>
                        <a href="<?= url('/admin/queue/failed') ?>" class="btn btn-outline-danger">
                            <i data-feather="alert-circle"></i> Failed Jobs
                        </a>
                        <a href="<?= url('/admin/queue/stats') ?>" class="btn btn-outline-info">
                            <i data-feather="bar-chart-2"></i> Statistics
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <?php if (!empty($activity)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <h5>Recent Activity</h5>
                        <span class="f-w-500 f-12 f-light mt-0">Last 10 jobs processed</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordernone">
                                <thead>
                                    <tr class="border-bottom-primary">
                                        <th scope="col">#ID</th>
                                        <th scope="col">Queue</th>
                                        <th scope="col">Job Class</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($activity as $job): ?>
                                        <tr>
                                            <td><code class="text-dark"><?= $job['id'] ?></code></td>
                                            <td><span class="badge badge-light-primary"><?= htmlspecialchars($job['queue']) ?></span></td>
                                            <td>
                                                <?php
                                                $payload = json_decode($job['payload'], true);
                                                $jobClass = $payload['job'] ?? 'Unknown';
                                                echo '<span class="f-light">' . htmlspecialchars(basename(str_replace('\\', '/', $jobClass))) . '</span>';
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($job['reserved_at']): ?>
                                                    <span class="badge badge-light-warning">
                                                        <i data-feather="loader" style="width: 12px; height: 12px;"></i> Processing
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-light-info">
                                                        <i data-feather="clock" style="width: 12px; height: 12px;"></i> Pending
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="f-light"><?= date('d/m/Y H:i', $job['created_at']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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