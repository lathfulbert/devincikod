@extends('backend.layouts.master')

@section('title', $title ?? 'Admin')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Active Jobs</h3>
            </div>
            <div class="col-6">
                <?php
                // Breadcrumb
                $breadcrumb = [
                    ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
                    ['label' => 'Queue', 'url' => '/admin/queue'],
                    ['label' => 'Active Jobs']
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
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5>Active Jobs in Queue</h5>
                            <span class="f-w-500 f-12 f-light mt-0">Total: <?= $pagination['total'] ?> job(s)</span>
                        </div>
                        <div>
                            <span class="badge badge-light-primary f-14">
                                <i data-feather="activity" style="width: 14px; height: 14px;"></i>
                                <?= $pagination['total'] ?> jobs
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($jobs)): ?>
                        <div class="alert alert-light-info">
                            <i data-feather="info"></i>
                            <span class="f-light">No active jobs in queue.</span>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordernone">
                                <thead>
                                    <tr class="border-bottom-primary">
                                        <th scope="col">#ID</th>
                                        <th scope="col">Queue</th>
                                        <th scope="col">Job Class</th>
                                        <th scope="col">Attempts</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($jobs as $job): ?>
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
                                                <span class="badge badge-light-secondary"><?= $job['attempts'] ?></span>
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
                                            <td><span class="f-light"><?= date('d/m/Y H:i', $job['created_at']) ?></span></td>
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
                                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
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