@extends('backend.layouts.master')

@section('title', 'Email Templates')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $title ?? 'Email Templates' ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/email-marketing') ?>">Email Marketing</a></li>
                    <li class="breadcrumb-item active">Templates</li>
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
                    <h5>Email Templates</h5>
                    <a href="<?= url('/admin/email-marketing/templates/create') ?>" class="btn btn-primary">
                        <i data-feather="plus"></i> New Template
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if (empty($templates)): ?>
                            <div class="col-12">
                                <p class="text-center text-muted py-5">No templates found.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($templates as $template): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card template-card h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <h5 class="card-title mb-0">
                                                    <?= htmlspecialchars($template->name) ?>
                                                </h5>
                                                <?php if ($template->is_active): ?>
                                                    <span class="badge badge-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </div>

                                            <?php if ($template->description): ?>
                                                <p class="card-text text-muted small">
                                                    <?= htmlspecialchars($template->description) ?>
                                                </p>
                                            <?php endif; ?>

                                            <div class="mb-3">
                                                <small class="text-muted">
                                                    <?php
                                                    $variables = $template->getVariables();
                                                    if (!empty($variables)):
                                                    ?>
                                                        <i data-feather="code"></i>
                                                        Variables:
                                                        <?php foreach ($variables as $var): ?>
                                                            <code class="badge badge-light">&lbrace;&lbrace;<?= htmlspecialchars($var) ?>&rbrace;&rbrace;</code>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <i data-feather="info"></i> No variables
                                                    <?php endif; ?>
                                                </small>
                                            </div>

                                            <div class="template-preview mb-3"
                                                style="max-height: 150px; overflow: hidden; border: 1px solid #eee; border-radius: 4px;">
                                                <iframe srcdoc="<?= htmlspecialchars($template->html_content) ?>"
                                                    style="width: 100%; height: 150px; border: none; transform: scale(0.8); transform-origin: 0 0;">
                                                </iframe>
                                            </div>

                                            <small class="text-muted d-block mb-3">
                                                <i data-feather="clock"></i>
                                                Created: <?= date('d/m/Y', strtotime($template->created_at)) ?>
                                            </small>

                                            <div class="btn-group btn-block" role="group">
                                                <a href="<?= url('/admin/email-marketing/templates/' . $template->id . '/preview') ?>"
                                                    class="btn btn-sm btn-info" title="Preview" target="_blank">
                                                    <i data-feather="eye"></i>
                                                </a>
                                                <a href="<?= url('/admin/email-marketing/templates/' . $template->id . '/edit') ?>"
                                                    class="btn btn-sm btn-warning" title="Edit">
                                                    <i data-feather="edit"></i>
                                                </a>
                                                <form method="POST"
                                                    action="<?= url('/admin/email-marketing/templates/' . $template->id . '/duplicate') ?>"
                                                    style="display: inline;">
                                                    <button type="submit" class="btn btn-sm btn-success" title="Duplicate">
                                                        <i data-feather="copy"></i>
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-primary"
                                                    data-toggle="modal" data-target="#testModal<?= $template->id ?>"
                                                    title="Send Test">
                                                    <i data-feather="send"></i>
                                                </button>
                                                <form method="POST"
                                                    action="<?= url('/admin/email-marketing/templates/' . $template->id . '/delete') ?>"
                                                    style="display: inline;"
                                                    onsubmit="return confirm('Are you sure you want to delete this template?');">
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                        <i data-feather="trash-2"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Test Email Modal -->
                                <div class="modal fade" id="testModal<?= $template->id ?>" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form method="POST"
                                                action="<?= url('/admin/email-marketing/templates/' . $template->id . '/test') ?>">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Send Test Email</h5>
                                                    <button type="button" class="close" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="test_email<?= $template->id ?>">Recipient Email</label>
                                                        <input type="email" class="form-control"
                                                            id="test_email<?= $template->id ?>"
                                                            name="test_email" required
                                                            placeholder="Enter test email address">
                                                    </div>

                                                    <?php if (!empty($template->getVariables())): ?>
                                                        <div class="alert alert-info">
                                                            <small>
                                                                <strong>Variables detected:</strong><br>
                                                                You can optionally provide test data (JSON format):
                                                            </small>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Test Data (JSON)</label>
                                                            <textarea class="form-control" name="test_data" rows="4"
                                                                placeholder='{"first_name": "John", "last_name": "Doe"}'></textarea>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                        Cancel
                                                    </button>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i data-feather="send"></i> Send Test
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
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