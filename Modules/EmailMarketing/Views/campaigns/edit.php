@extends('backend.layouts.master')

@section('title', 'Edit Campaign')

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
    <form method="POST" action="<?= url('/admin/email-marketing/campaigns/' . $campaign->id) ?>">
        <input type="hidden" name="_method" value="PUT">

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
                                   value="<?= htmlspecialchars($campaign->name) ?>"
                                   placeholder="Enter campaign name">
                        </div>

                        <div class="form-group">
                            <label for="subject">Email Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subject" name="subject" required
                                   value="<?= htmlspecialchars($campaign->subject) ?>"
                                   placeholder="Enter email subject">
                        </div>

                        <div class="form-group">
                            <label for="template_id">Email Template</label>
                            <select class="form-control" id="template_id" name="template_id">
                                <option value="">-- Select Template --</option>
                                <?php foreach ($templates as $template): ?>
                                    <option value="<?= $template->id ?>"
                                            <?= $campaign->template_id == $template->id ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($template->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="use_personalization"
                                       name="use_personalization" value="1"
                                       <?= $campaign->use_personalization ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="use_personalization">
                                    Enable Personalization
                                </label>
                            </div>
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
                                   value="<?= htmlspecialchars($campaign->from_name ?? '') ?>"
                                   placeholder="e.g., John Doe">
                        </div>

                        <div class="form-group">
                            <label for="from_email">From Email</label>
                            <input type="email" class="form-control" id="from_email" name="from_email"
                                   value="<?= htmlspecialchars($campaign->from_email ?? '') ?>"
                                   placeholder="e.g., john@example.com">
                        </div>

                        <div class="form-group">
                            <label for="reply_to">Reply-To Email</label>
                            <input type="email" class="form-control" id="reply_to" name="reply_to"
                                   value="<?= htmlspecialchars($campaign->reply_to ?? '') ?>"
                                   placeholder="e.g., support@example.com">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Campaign Status</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $statusClass = match($campaign->status) {
                            'draft' => 'secondary',
                            'scheduled' => 'info',
                            'sending' => 'warning',
                            'completed' => 'success',
                            'paused' => 'dark',
                            'failed' => 'danger',
                            default => 'secondary'
                        };
                        ?>
                        <span class="badge badge-<?= $statusClass ?> badge-lg">
                            <?= ucfirst($campaign->status) ?>
                        </span>

                        <?php if (!in_array($campaign->status, ['draft', 'scheduled'])): ?>
                            <div class="alert alert-warning mt-3">
                                <small>
                                    <i data-feather="alert-triangle"></i>
                                    Campaigns can only be edited in draft or scheduled status.
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5>Schedule</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="scheduled_at">Schedule For</label>
                            <input type="datetime-local" class="form-control" id="scheduled_at" name="scheduled_at"
                                   value="<?= $campaign->scheduled_at ? date('Y-m-d\TH:i', strtotime($campaign->scheduled_at)) : '' ?>">
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i data-feather="save"></i> Update Campaign
                        </button>
                        <a href="<?= url('/admin/email-marketing/campaigns/' . $campaign->id) ?>"
                           class="btn btn-secondary btn-block">
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
