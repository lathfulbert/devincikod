@extends('backend.layouts.master')

@section('title', 'Add New Sender Name')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $title ?? 'Add New Sender Name' ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/sms') ?>">SMS</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/sms/sender-names') ?>">Sender Names</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['flash_error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h5><i data-feather="plus"></i> Add New Sender Name</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= url('/sms/sender-names/store') ?>">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="name" class="form-label">
                                Sender Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="name"
                                   name="name"
                                   maxlength="11"
                                   required
                                   placeholder="e.g. MYCOMPANY"
                                   style="text-transform: uppercase;">
                            <small class="form-text text-muted">
                                Maximum 11 characters. Will be converted to uppercase. This is what appears on recipients' phones.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="operator" class="form-label">Operator</label>
                            <select class="form-control" id="operator" name="operator">
                                <option value="">-- Select Operator (Optional) --</option>
                                <option value="Orange CI">Orange CI</option>
                                <option value="MTN CI">MTN CI</option>
                                <option value="Moov CI">Moov CI</option>
                                <option value="Other">Other</option>
                            </select>
                            <small class="form-text text-muted">
                                Select the mobile operator that validated this sender name (if applicable).
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">
                                Status <span class="text-danger">*</span>
                            </label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                            <small class="form-text text-muted">
                                <strong>Pending:</strong> Awaiting operator validation<br>
                                <strong>Approved:</strong> Validated by operator and ready to use<br>
                                <strong>Rejected:</strong> Not approved by operator
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="validation_date" class="form-label">Validation Date</label>
                            <input type="date"
                                   class="form-control"
                                   id="validation_date"
                                   name="validation_date">
                            <small class="form-text text-muted">
                                Date when the operator validated this sender name (optional).
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control"
                                      id="notes"
                                      name="notes"
                                      rows="3"
                                      placeholder="Any additional information about this sender name..."></textarea>
                            <small class="form-text text-muted">
                                Internal notes about this sender name (optional).
                            </small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="is_active"
                                       name="is_active"
                                       value="1"
                                       checked>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Only active sender names can be used for sending SMS.
                            </small>
                        </div>

                        <div class="alert alert-info">
                            <i data-feather="info"></i>
                            <strong>Important:</strong> After creating a sender name, you need to assign it to users before they can use it for sending SMS.
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="save"></i> Create Sender Name
                            </button>
                            <a href="<?= url('/sms/sender-names') ?>" class="btn btn-secondary">
                                <i data-feather="x"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6><i data-feather="help-circle"></i> Sender Name Guidelines</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Sender names must be <strong>11 characters or less</strong></li>
                        <li>Use <strong>alphanumeric characters only</strong> (A-Z, 0-9)</li>
                        <li>Avoid special characters and spaces</li>
                        <li>Choose a name that <strong>identifies your organization</strong></li>
                        <li>Must be <strong>validated by the mobile operator</strong> before use</li>
                        <li>Each operator may have different validation requirements</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    feather.replace();

    // Auto-uppercase sender name input
    document.getElementById('name').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });

    // Character counter
    document.getElementById('name').addEventListener('input', function() {
        const length = this.value.length;
        const maxLength = 11;

        if (length > maxLength) {
            this.value = this.value.substring(0, maxLength);
        }
    });
</script>
@endsection
