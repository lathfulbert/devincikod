@extends('backend.layouts.master')

@section('title', 'Email Workflows')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $title ?? 'Email Workflows' ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/email-marketing') ?>">Email Marketing</a></li>
                    <li class="breadcrumb-item active">Workflows</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php component('alerts'); ?>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Automated Workflows</h5>
                    <a href="<?= route('admin.email-marketing.workflows.create') ?>" class="btn btn-primary">
                        <i data-feather="plus"></i> New Workflow
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Trigger</th>
                                    <th>Steps</th>
                                    <th>Status</th>
                                    <th>Executions</th>
                                    <th>Success Rate</th>
                                    <th>Last Run</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($workflows)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">No workflows found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($workflows as $workflow): ?>
                                        <?php
                                        $steps = json_decode($workflow->steps ?? '[]', true);
                                        $statusClass = match($workflow->status) {
                                            'draft' => 'secondary',
                                            'active' => 'success',
                                            'paused' => 'warning',
                                            'archived' => 'dark',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($workflow->name) ?></strong></td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?= ucfirst(str_replace('_', ' ', $workflow->trigger_type)) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary"><?= count($steps) ?> steps</span>
                                                <br>
                                                <small class="text-muted">
                                                    <?php
                                                    $channels = array_unique(array_column($steps, 'channel'));
                                                    echo implode(', ', array_map('ucfirst', $channels));
                                                    ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $statusClass ?>">
                                                    <?= ucfirst($workflow->status) ?>
                                                </span>
                                            </td>
                                            <td><?= number_format($workflow->total_executions ?? 0) ?></td>
                                            <td>
                                                <?php
                                                $total = $workflow->total_executions ?? 0;
                                                $completed = $workflow->completed_executions ?? 0;
                                                $successRate = $total > 0 ? round(($completed / $total) * 100) : 0;
                                                ?>
                                                <?= $successRate ?>%
                                            </td>
                                            <td>
                                                <?php if ($workflow->last_run_at): ?>
                                                    <?= date('d/m/Y H:i', strtotime($workflow->last_run_at)) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Never</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?= route('admin.email-marketing.workflows.show', ['id' => $workflow->id]) ?>"
                                                       class="btn btn-sm btn-info" title="View">
                                                        <i data-feather="eye"></i>
                                                    </a>

                                                    <?php if ($workflow->status !== 'archived'): ?>
                                                        <a href="<?= route('admin.email-marketing.workflows.edit', ['id' => $workflow->id]) ?>"
                                                           class="btn btn-sm btn-warning" title="Edit">
                                                            <i data-feather="edit"></i>
                                                        </a>
                                                    <?php endif; ?>

                                                    <?php if ($workflow->status === 'draft' || $workflow->status === 'paused'): ?>
                                                        <form method="POST"
                                                              action="<?= route('admin.email-marketing.workflows.activate', ['id' => $workflow->id]) ?>"
                                                              style="display: inline;">
                                                            <button type="submit" class="btn btn-sm btn-success" title="Activate">
                                                                <i data-feather="play"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <?php if ($workflow->status === 'active'): ?>
                                                        <form method="POST"
                                                              action="<?= route('admin.email-marketing.workflows.pause', ['id' => $workflow->id]) ?>"
                                                              style="display: inline;">
                                                            <button type="submit" class="btn btn-sm btn-warning" title="Pause">
                                                                <i data-feather="pause"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <button type="button" class="btn btn-sm btn-primary"
                                                            data-toggle="modal" data-target="#executeModal<?= $workflow->id ?>"
                                                            title="Execute">
                                                        <i data-feather="zap"></i>
                                                    </button>

                                                    <?php if ($workflow->status === 'draft'): ?>
                                                        <form method="POST"
                                                              action="<?= route('admin.email-marketing.workflows.delete', ['id' => $workflow->id]) ?>"
                                                              style="display: inline;"
                                                              onsubmit="return confirm('Are you sure you want to delete this workflow?');">
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                                <i data-feather="trash-2"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Execute Modal -->
                                        <div class="modal fade" id="executeModal<?= $workflow->id ?>" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <form method="POST"
                                                          action="<?= route('admin.email-marketing.workflows.execute', ['id' => $workflow->id]) ?>">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Execute Workflow</h5>
                                                            <button type="button" class="close" data-dismiss="modal">
                                                                <span>&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Execute <strong><?= htmlspecialchars($workflow->name) ?></strong> for selected contacts.</p>
                                                            <div class="form-group">
                                                                <label for="contact_ids<?= $workflow->id ?>">Contact IDs (comma-separated)</label>
                                                                <textarea class="form-control" id="contact_ids<?= $workflow->id ?>"
                                                                          name="contact_ids" rows="3" required
                                                                          placeholder="1,2,3,4,5"></textarea>
                                                                <small class="form-text text-muted">
                                                                    Enter contact IDs separated by commas
                                                                </small>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                                Cancel
                                                            </button>
                                                            <button type="submit" class="btn btn-primary">
                                                                <i data-feather="zap"></i> Execute
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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
