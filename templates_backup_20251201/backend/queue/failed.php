@extends('backend.layouts.master')

@section('title', $title ?? 'Admin')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Failed Jobs</h3>
            </div>
            <div class="col-6">
                <?php
                // Breadcrumb
                $breadcrumb = [
                    ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
                    ['label' => 'Queue', 'url' => '/admin/queue'],
                    ['label' => 'Failed Jobs']
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
                            <h5>Failed Jobs</h5>
                            <span class="f-w-500 f-12 f-light mt-0">Total: <?= $pagination['total'] ?> failed job(s)</span>
                        </div>
                        <?php if (!empty($jobs)): ?>
                            <div>
                                <form method="POST" action="<?= url('/admin/queue/retry-all') ?>" style="display:inline;">
                                    <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
                                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Retry all failed jobs?')">
                                        <i data-feather="refresh-cw"></i> Retry All
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($jobs)): ?>
                        <div class="alert alert-light-success">
                            <i data-feather="check-circle"></i>
                            <span class="f-light">No failed jobs! Everything is running smoothly.</span>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordernone">
                                <thead>
                                    <tr class="border-bottom-danger">
                                        <th scope="col">#ID</th>
                                        <th scope="col">Queue</th>
                                        <th scope="col">Job Class</th>
                                        <th scope="col">Exception</th>
                                        <th scope="col">Failed At</th>
                                        <th scope="col" class="text-end">Actions</th>
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
                                                <div class="text-danger f-light" title="<?= htmlspecialchars($job['exception']) ?>">
                                                    <i data-feather="alert-triangle" style="width: 14px; height: 14px;"></i>
                                                    <span class="text-truncate d-inline-block" style="max-width: 300px;">
                                                        <?= htmlspecialchars(substr($job['exception'], 0, 50)) ?>...
                                                    </span>
                                                </div>
                                            </td>
                                            <td><span class="f-light"><?= date('d/m/Y H:i', strtotime($job['failed_at'])) ?></span></td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <form method="POST" action="<?= url('/admin/queue/retry') ?>" style="display:inline;">
                                                        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
                                                        <input type="hidden" name="id" value="<?= $job['id'] ?>">
                                                        <button type="submit" class="btn btn-success" title="Retry">
                                                            <i data-feather="refresh-cw" style="width: 14px; height: 14px;"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="<?= url('/admin/queue/delete') ?>" style="display:inline;">
                                                        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
                                                        <input type="hidden" name="id" value="<?= $job['id'] ?>">
                                                        <button type="submit" class="btn btn-danger" title="Delete" onclick="return confirm('Delete this job?')">
                                                            <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                                        </button>
                                                    </form>
                                                </div>
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