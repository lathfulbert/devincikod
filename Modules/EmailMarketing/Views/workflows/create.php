@extends('backend.layouts.master')

@section('title', 'Create Workflow')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $title ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= route('home') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= route('admin.email-marketing.index') ?>">Email Marketing</a></li>
                    <li class="breadcrumb-item"><a href="<?= route('admin.email-marketing.workflows.index') ?>">Workflows</a></li>
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
    <form method="POST" action="<?= route('admin.email-marketing.workflows.store') ?>" id="workflowForm">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Workflow Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Workflow Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required
                                placeholder="Enter workflow name">
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                placeholder="Describe what this workflow does"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="trigger_type">Trigger Type <span class="text-danger">*</span></label>
                            <select class="form-control" id="trigger_type" name="trigger_type" required>
                                <option value="">-- Select Trigger --</option>
                                <option value="manual">Manual (Execute on demand)</option>
                                <option value="contact_created">When Contact is Created</option>
                                <option value="contact_updated">When Contact is Updated</option>
                                <option value="scheduled">Scheduled (Recurring)</option>
                                <option value="webhook">Webhook Triggered</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Workflow Steps</h5>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addStep()">
                            <i data-feather="plus"></i> Add Step
                        </button>
                    </div>
                    <div class="card-body" id="stepsContainer">
                        <p class="text-muted text-center py-4">No steps added yet. Click "Add Step" to begin.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Workflow Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Status</label>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="status_draft" name="status" value="draft" class="custom-control-input" checked>
                                <label class="custom-control-label" for="status_draft">Draft</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="status_active" name="status" value="active" class="custom-control-input">
                                <label class="custom-control-label" for="status_active">Active</label>
                            </div>
                            <small class="form-text text-muted">Active workflows can be triggered automatically</small>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5>Quick Info</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <small>
                                <strong>Available Channels:</strong><br>
                                • Email - Send email messages<br>
                                • SMS - Send text messages<br>
                                <br>
                                <strong>Delays:</strong><br>
                                Configure delays between steps in seconds.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i data-feather="save"></i> Create Workflow
                        </button>
                        <a href="<?= route('admin.email-marketing.workflows.index') ?>" class="btn btn-secondary btn-block">
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

    let stepCounter = 0;

    function addStep() {
        stepCounter++;
        const container = document.getElementById('stepsContainer');

        // Remove "no steps" message if it exists
        if (container.querySelector('p.text-muted')) {
            container.innerHTML = '';
        }

        const stepHtml = `
            <div class="card mb-3 step-card" id="step${stepCounter}">
                <div class="card-header d-flex justify-content-between align-items-center bg-light">
                    <h6 class="mb-0">Step ${stepCounter}</h6>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeStep(${stepCounter})">
                        <i data-feather="trash-2"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Channel <span class="text-danger">*</span></label>
                        <select class="form-control" name="step[${stepCounter - 1}][channel]" required onchange="toggleChannelFields(${stepCounter})">
                            <option value="">-- Select Channel --</option>
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                        </select>
                    </div>

                    <div class="email-fields" id="emailFields${stepCounter}" style="display: none;">
                        <div class="form-group">
                            <label>Email Template</label>
                            <select class="form-control" name="step[${stepCounter - 1}][template_id]">
                                <option value="">-- Select Template --</option>
                                <?php foreach ($templates as $template): ?>
                                    <option value="<?= $template->id ?>">
                                        <?= htmlspecialchars($template->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="sms-fields" id="smsFields${stepCounter}" style="display: none;">
                        <div class="form-group">
                            <label>SMS Message</label>
                            <textarea class="form-control" name="step[${stepCounter - 1}][message]" rows="3"
                                      placeholder="Enter SMS message (supports variables like &#123;&#123;first_name&#125;&#125;)"></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Delay After This Step (seconds)</label>
                        <input type="number" class="form-control" name="step[${stepCounter - 1}][delay]"
                               value="0" min="0" placeholder="0">
                        <small class="form-text text-muted">
                            Wait time before next step. 0 = immediate
                        </small>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input"
                               id="stopOnFailure${stepCounter}" name="step[${stepCounter - 1}][stop_on_failure]" value="1">
                        <label class="custom-control-label" for="stopOnFailure${stepCounter}">
                            Stop workflow if this step fails
                        </label>
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', stepHtml);
        feather.replace();
    }

    function removeStep(stepId) {
        const step = document.getElementById('step' + stepId);
        if (step) {
            step.remove();
        }

        // Show "no steps" message if all steps removed
        const container = document.getElementById('stepsContainer');
        if (!container.querySelector('.step-card')) {
            container.innerHTML = '<p class="text-muted text-center py-4">No steps added yet. Click "Add Step" to begin.</p>';
        }
    }

    function toggleChannelFields(stepId) {
        const select = document.querySelector(`#step${stepId} select[name*="[channel]"]`);
        const emailFields = document.getElementById('emailFields' + stepId);
        const smsFields = document.getElementById('smsFields' + stepId);

        if (select.value === 'email') {
            emailFields.style.display = 'block';
            smsFields.style.display = 'none';
        } else if (select.value === 'sms') {
            emailFields.style.display = 'none';
            smsFields.style.display = 'block';
        } else {
            emailFields.style.display = 'none';
            smsFields.style.display = 'none';
        }
    }

    // Form validation
    document.getElementById('workflowForm').addEventListener('submit', function(e) {
        const container = document.getElementById('stepsContainer');
        if (!container.querySelector('.step-card')) {
            e.preventDefault();
            alert('Please add at least one step to the workflow.');
            return false;
        }
    });
</script>
@endsection