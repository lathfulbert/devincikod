@extends('backend.layouts.master')

@section('title', $title)

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $title ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/email-marketing') ?>">Email Marketing</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/email-marketing/templates') ?>">Templates</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($template->name) ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php component('alerts'); ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Preview</h5>
                    <a href="<?= url('/admin/email-marketing/templates/' . $template->id . '/preview') ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i data-feather="external-link"></i> Full Screen
                    </a>
                </div>
                <div class="card-body bg-light p-0">
                    <div class="embed-responsive embed-responsive-16by9" style="height: 600px; border: 1px solid #ddd;">
                        <iframe src="<?= url('/admin/email-marketing/templates/' . $template->id . '/preview') ?>" style="width: 100%; height: 100%; border: none;"></iframe>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <th width="40%">Status:</th>
                            <td>
                                <?php if ($template->is_active): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Category:</th>
                            <td><?= ucfirst(htmlspecialchars($template->category ?? 'N/A')) ?></td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td><?= date('d/m/Y H:i', strtotime($template->created_at)) ?></td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td><?= $template->updated_at ? date('d/m/Y H:i', strtotime($template->updated_at)) : '-' ?></td>
                        </tr>
                        <tr>
                            <th>Usage:</th>
                            <td><?= $usageCount ?> campaigns</td>
                        </tr>
                    </table>

                    <hr>

                    <h6>Variables Detected:</h6>
                    <?php if (!empty($variables)): ?>
                        <div class="mb-3">
                            <?php foreach ($variables as $var): ?>
                                <span class="badge badge-light border mb-1">&#123;&#123;<?= htmlspecialchars($var) ?>&#125;&#125;</span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small">No variables detected.</p>
                    <?php endif; ?>

                    <hr>

                    <div class="d-grid gap-2">
                        <a href="<?= url('/admin/email-marketing/templates/' . $template->id . '/edit') ?>" class="btn btn-primary btn-block">
                            <i data-feather="edit"></i> Edit Template
                        </a>

                        <button type="button" class="btn btn-info btn-block" data-toggle="modal" data-target="#testModal">
                            <i data-feather="send"></i> Send Test Email
                        </button>

                        <form method="POST" action="<?= url('/admin/email-marketing/templates/' . $template->id . '/duplicate') ?>" style="display:inline;">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-warning btn-block text-white">
                                <i data-feather="copy"></i> Duplicate
                            </button>
                        </form>

                        <a href="<?= url('/admin/email-marketing/templates/' . $template->id . '/delete') ?>"
                            class="btn btn-danger btn-block"
                            onclick="return confirm('Are you sure you want to delete this template? This action cannot be undone.')">
                            <i data-feather="trash-2"></i> Delete
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test Email Modal -->
<div class="modal fade" id="testModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="<?= url('/admin/email-marketing/templates/' . $template->id . '/test') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Send Test Email</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="test_email">Recipient Email</label>
                        <input type="email" class="form-control" id="test_email" name="test_email" required
                            placeholder="Enter test email address">
                    </div>

                    <?php if (!empty($variables)): ?>
                        <div class="alert alert-info py-2">
                            <small>
                                <strong>Variables:</strong> Default test values will be used.
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="send"></i> Send Test
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    feather.replace();
</script>
@endsection