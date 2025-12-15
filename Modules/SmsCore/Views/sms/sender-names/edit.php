@extends('backend.layouts.master')

@section('title', 'Edit Sender Name')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $title ?? 'Edit Sender Name' ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/sms') ?>">SMS</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/sms/sender-names') ?>">Sender Names</a></li>
                    <li class="breadcrumb-item active">Edit</li>
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
                    <h5><i data-feather="edit"></i> Edit Sender Name</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= url('/admin/sms/sender-names/update') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $senderName->id ?>">

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
                                   value="<?= htmlspecialchars($senderName->name ?? '') ?>"
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
                                <option value="Orange CI" <?= ($senderName->operator ?? '') === 'Orange CI' ? 'selected' : '' ?>>Orange CI</option>
                                <option value="MTN CI" <?= ($senderName->operator ?? '') === 'MTN CI' ? 'selected' : '' ?>>MTN CI</option>
                                <option value="Moov CI" <?= ($senderName->operator ?? '') === 'Moov CI' ? 'selected' : '' ?>>Moov CI</option>
                                <option value="Other" <?= ($senderName->operator ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
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
                                <option value="pending" <?= ($senderName->status ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="approved" <?= ($senderName->status ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                                <option value="rejected" <?= ($senderName->status ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
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
                                   name="validation_date"
                                   value="<?= !empty($senderName->validation_date) ? date('Y-m-d', strtotime($senderName->validation_date)) : '' ?>">
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
                                      placeholder="Any additional information about this sender name..."><?= htmlspecialchars($senderName->notes ?? '') ?></textarea>
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
                                       <?= !empty($senderName->is_active) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Only active sender names can be used for sending SMS.
                            </small>
                        </div>

                        <?php if (!empty($senderName->created_at)): ?>
                        <div class="alert alert-light">
                            <small class="text-muted">
                                <i data-feather="clock"></i>
                                <strong>Created:</strong> <?= date('d/m/Y H:i', strtotime($senderName->created_at)) ?>
                                <?php if (!empty($senderName->updated_at) && $senderName->updated_at !== $senderName->created_at): ?>
                                    <br><strong>Last Updated:</strong> <?= date('d/m/Y H:i', strtotime($senderName->updated_at)) ?>
                                <?php endif; ?>
                            </small>
                        </div>
                        <?php endif; ?>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="save"></i> Update Sender Name
                            </button>
                            <a href="<?= url('/admin/sms/sender-names') ?>" class="btn btn-secondary">
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
