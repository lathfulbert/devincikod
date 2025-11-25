@extends('backend.layouts.master')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Queue Statistics</h3>
            </div>
            <div class="col-6">
                <?php
                // Breadcrumb
                $breadcrumb = [
                    ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
                    ['label' => 'Queue', 'url' => '/admin/queue'],
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
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5>Queue Statistics</h5>
                            <span class="f-w-500 f-12 f-light mt-0">Performance metrics and trends</span>
                        </div>
                        <div class="btn-group btn-group-pill btn-group-sm" role="group">
                            <a href="?period=24h" class="btn btn-<?= $period === '24h' ? '' : 'outline-' ?>primary">24 Hours</a>
                            <a href="?period=7d" class="btn btn-<?= $period === '7d' ? '' : 'outline-' ?>primary">7 Days</a>
                            <a href="?period=30d" class="btn btn-<?= $period === '30d' ? '' : 'outline-' ?>primary">30 Days</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($stats['jobs_over_time']) && empty($stats['failed_over_time'])): ?>
                        <div class="alert alert-light-info">
                            <i data-feather="info"></i>
                            <span class="f-light">No data available for selected period.</span>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordernone">
                                <thead>
                                    <tr class="border-bottom-primary">
                                        <th scope="col">Time Slot</th>
                                        <th scope="col">Jobs Created</th>
                                        <th scope="col">Jobs Failed</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Merge data
                                    $timeSlots = [];
                                    foreach ($stats['jobs_over_time'] as $job) {
                                        $timeSlots[$job['time_slot']]['created'] = $job['count'];
                                    }
                                    foreach ($stats['failed_over_time'] ?? [] as $failed) {
                                        $timeSlots[$failed['time_slot']]['failed'] = $failed['count'];
                                    }
                                    ksort($timeSlots);

                                    foreach ($timeSlots as $time => $data):
                                    ?>
                                        <tr>
                                            <td><span class="f-light"><?= $time ?></span></td>
                                            <td><span class="badge badge-light-primary"><?= $data['created'] ?? 0 ?></span></td>
                                            <td><span class="badge badge-light-danger"><?= $data['failed'] ?? 0 ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Visual Chart -->
                        <div class="mt-4">
                            <h6 class="f-w-600">Visual Overview</h6>
                            <?php
                            $max = max(array_column($timeSlots, 'created'));
                            foreach ($timeSlots as $time => $data):
                                $created = $data['created'] ?? 0;
                                $barWidth = $max > 0 ? ($created / $max) * 100 : 0;
                            ?>
                                <div class="mb-2">
                                    <small class="f-light f-12"><?= date('H:i', strtotime($time)) ?></small>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-primary progress-bar-striped" style="width: <?= $barWidth ?>%">
                                            <?= $created ?>
                                        </div>
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
    <div class="row mt-3">
        <div class="col-md-4">
            <div class="card o-hidden">
                <div class="bg-primary b-r-4 card-body">
                    <div class="media static-top-widget">
                        <div class="align-self-center text-center">
                            <i data-feather="package" style="width: 36px; height: 36px;"></i>
                        </div>
                        <div class="media-body">
                            <span class="m-0">Total Jobs</span>
                            <h4 class="mb-0"><?= array_sum(array_column($stats['jobs_over_time'], 'count')) ?></h4>
                            <i data-feather="package" class="icon-bg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card o-hidden">
                <div class="bg-danger b-r-4 card-body">
                    <div class="media static-top-widget">
                        <div class="align-self-center text-center">
                            <i data-feather="x-circle" style="width: 36px; height: 36px;"></i>
                        </div>
                        <div class="media-body">
                            <span class="m-0">Failed Jobs</span>
                            <h4 class="mb-0"><?= array_sum(array_column($stats['failed_over_time'] ?? [], 'count')) ?></h4>
                            <i data-feather="x-circle" class="icon-bg"></i>
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
                            <i data-feather="trending-up" style="width: 36px; height: 36px;"></i>
                        </div>
                        <div class="media-body">
                            <span class="m-0">Success Rate</span>
                            <?php
                            $totalJobs = array_sum(array_column($stats['jobs_over_time'], 'count'));
                            $totalFailed = array_sum(array_column($stats['failed_over_time'] ?? [], 'count'));
                            $successRate = $totalJobs > 0 ? round((($totalJobs - $totalFailed) / $totalJobs) * 100, 1) : 100;
                            ?>
                            <h4 class="mb-0"><?= $successRate ?>%</h4>
                            <i data-feather="trending-up" class="icon-bg"></i>
                        </div>
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