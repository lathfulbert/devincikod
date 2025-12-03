@extends('backend.layouts.master')

@section('title', 'Create Email Campaign')

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
                    <li class="breadcrumb-item"><a href="<?= url('/admin/email-marketing/campaigns') ?>">Campaigns</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php component('alerts'); ?>
</div>

<div class="container-fluid">
    <form method="POST" action="<?= url('/admin/email-marketing/campaigns') ?>">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Campaign Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Campaign Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required
                                   placeholder="Enter campaign name">
                            <small class="form-text text-muted">Internal name for identifying this campaign</small>
                        </div>

                        <div class="form-group">
                            <label for="subject">Email Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subject" name="subject" required
                                   placeholder="Enter email subject">
                            <small class="form-text text-muted">The subject line recipients will see</small>
                        </div>

                        <div class="form-group">
                            <label for="template_id">Email Template</label>
                            <select class="form-control" id="template_id" name="template_id">
                                <option value="">-- Select Template --</option>
                                <?php foreach ($templates as $template): ?>
                                    <option value="<?= $template->id ?>">
                                        <?= htmlspecialchars($template->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Choose a pre-designed template (optional)</small>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="use_personalization"
                                       name="use_personalization" value="1">
                                <label class="custom-control-label" for="use_personalization">
                                    Enable Personalization
                                </label>
                            </div>
                            <small class="form-text text-muted">Allow variable substitution like {{first_name}}</small>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5>Sender Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="from_name">From Name</label>
                            <input type="text" class="form-control" id="from_name" name="from_name"
                                   placeholder="e.g., John Doe">
                            <small class="form-text text-muted">Leave empty to use default from settings</small>
                        </div>

                        <div class="form-group">
                            <label for="from_email">From Email</label>
                            <input type="email" class="form-control" id="from_email" name="from_email"
                                   placeholder="e.g., john@example.com">
                            <small class="form-text text-muted">Leave empty to use default from settings</small>
                        </div>

                        <div class="form-group">
                            <label for="reply_to">Reply-To Email</label>
                            <input type="email" class="form-control" id="reply_to" name="reply_to"
                                   placeholder="e.g., support@example.com">
                            <small class="form-text text-muted">Where replies will be sent</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Schedule</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="scheduled_at">Schedule For (Optional)</label>
                            <input type="datetime-local" class="form-control" id="scheduled_at" name="scheduled_at">
                            <small class="form-text text-muted">Leave empty to save as draft</small>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5>Recipients</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            <i data-feather="info"></i> Recipients will be selected after creating the campaign.
                        </p>
                        <small class="text-muted">
                            You can select contacts from your contact list in the next step.
                        </small>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i data-feather="save"></i> Create Campaign
                        </button>
                        <a href="<?= url('/admin/email-marketing/campaigns') ?>" class="btn btn-secondary btn-block">
                            <i data-feather="x"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
    feather.replace();
</script>
@endsection
