@extends('backend.layouts.master')

@section('title', 'Edit Template')

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
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php component('alerts'); ?>
</div>

<div class="container-fluid">
    <form method="POST" action="<?= url('/admin/email-marketing/templates/' . $template->id . '/update') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="_method" value="PUT">

        <div class="row">
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h5>Template Content</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Template Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required
                                value="<?= htmlspecialchars($template->name) ?>"
                                placeholder="Enter template name">
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <input type="text" class="form-control" id="description" name="description"
                                value="<?= htmlspecialchars($template->description ?? '') ?>"
                                placeholder="Brief description of this template">
                        </div>

                        <div class="form-group">
                            <label for="html_content">HTML Content <span class="text-danger">*</span></label>
                            <div class="alert alert-info">
                                <small>
                                    <strong>Detected Variables:</strong>
                                    <?php
                                    $variables = $template->getVariables();
                                    if (!empty($variables)):
                                        foreach ($variables as $var):
                                    ?>
                                            <code class="badge badge-light">&#123;&#123;<?= htmlspecialchars($var) ?>&#125;&#125;</code>
                                        <?php
                                        endforeach;
                                    else:
                                        ?>
                                        <span class="text-muted">No variables found</span>
                                    <?php endif; ?>
                                </small>
                            </div>
                            <textarea id="html_content" name="html_content" class="form-control"><?= htmlspecialchars($template->html_content) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5>Template Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select class="form-control" id="category" name="category">
                                <option value="">-- Select Category --</option>
                                <option value="newsletter" <?= $template->category === 'newsletter' ? 'selected' : '' ?>>Newsletter</option>
                                <option value="promotional" <?= $template->category === 'promotional' ? 'selected' : '' ?>>Promotional</option>
                                <option value="transactional" <?= $template->category === 'transactional' ? 'selected' : '' ?>>Transactional</option>
                                <option value="welcome" <?= $template->category === 'welcome' ? 'selected' : '' ?>>Welcome</option>
                                <option value="follow-up" <?= $template->category === 'follow-up' ? 'selected' : '' ?>>Follow-up</option>
                                <option value="other" <?= $template->category === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_active"
                                    name="is_active" value="1" <?= $template->is_active ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="is_active">
                                    Active
                                </label>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-2">
                            <small class="text-muted">
                                <i data-feather="calendar"></i>
                                Created: <?= date('d/m/Y H:i', strtotime($template->created_at)) ?>
                            </small>
                        </div>
                        <?php if ($template->updated_at): ?>
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i data-feather="clock"></i>
                                    Updated: <?= date('d/m/Y H:i', strtotime($template->updated_at)) ?>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5>Actions</h5>
                    </div>
                    <div class="card-body">
                        <a href="<?= url('/admin/email-marketing/templates/' . $template->id . '/preview') ?>"
                            class="btn btn-info btn-block mb-2" target="_blank">
                            <i data-feather="eye"></i> Preview
                        </a>
                        <button type="button" class="btn btn-primary btn-block mb-2"
                            data-toggle="modal" data-target="#testModal">
                            <i data-feather="send"></i> Send Test
                        </button>
                        <form method="POST"
                            action="<?= url('/admin/email-marketing/templates/' . $template->id . '/duplicate') ?>"
                            style="display: inline; width: 100%;">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-success btn-block mb-2">
                                <i data-feather="copy"></i> Duplicate
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i data-feather="save"></i> Update Template
                        </button>
                        <a href="<?= url('/admin/email-marketing/templates') ?>" class="btn btn-secondary btn-block">
                            <i data-feather="x"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
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
                        <div class="alert alert-info">
                            <small>
                                <strong>Variables detected:</strong><br>
                                Provide test data (JSON format):
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
<!-- TinyMCE -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    feather.replace();

    // Initialize TinyMCE
    tinymce.init({
        selector: '#html_content',
        height: 500,
        plugins: 'code preview link image lists table wordcount',
        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code preview',
        menubar: 'file edit view insert format tools table',
        content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }',
        setup: function(editor) {
            editor.ui.registry.addButton('insertVariable', {
                text: 'Insert Variable',
                onAction: function() {
                    const variable = prompt('Enter variable name (e.g., first_name):');
                    if (variable) {
                        editor.insertContent('{' + '{' + variable + '}' + '}');
                    }
                }
            });
        }
    });
</script>
@endsection