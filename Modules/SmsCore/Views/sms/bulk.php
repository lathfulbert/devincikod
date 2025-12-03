@extends('backend.layouts.master')

@section('title', 'Send Bulk SMS')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $title ?? 'Send Bulk SMS' ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/sms') ?>">SMS</a></li>
                    <li class="breadcrumb-item active">Bulk Send</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5>Bulk SMS Campaign</h5>
                </div>
                <div class="card-body">
                    <form action="<?= url('/admin/sms/bulk') ?>" method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label class="form-label">Campaign Name</label>
                            <input class="form-control" type="text" name="campaign_name" placeholder="e.g. Weekly Newsletter" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Recipients Source</label>
                            <select class="form-select" name="source" id="recipientSource">
                                <option value="csv">Upload CSV File</option>
                                <option value="group">Contact Group</option>
                                <option value="manual">Manual Input (Comma separated)</option>
                            </select>
                        </div>

                        <div class="mb-3" id="csvUpload">
                            <label class="form-label">Upload CSV (Phone numbers in first column)</label>
                            <input class="form-control" type="file" name="recipients_file" accept=".csv">
                        </div>

                        <div class="mb-3 d-none" id="manualInput">
                            <label class="form-label">Phone Numbers</label>
                            <textarea class="form-control" name="recipients_manual" rows="3" placeholder="+1234567890, +0987654321"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sender Name <span class="text-danger">*</span></label>
                            <select class="form-control" id="sender_name_id" name="sender_name_id" required>
                                <option value="">-- Select Sender Name --</option>
                                <?php if (isset($senderNames) && !empty($senderNames)): ?>
                                    <?php foreach ($senderNames as $senderName): ?>
                                        <option value="<?= $senderName->id ?>">
                                            <?= htmlspecialchars($senderName->name) ?>
                                            <?php if ($senderName->operator): ?>
                                                (<?= htmlspecialchars($senderName->operator) ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No sender names assigned to you</option>
                                <?php endif; ?>
                            </select>
                            <small class="form-text text-muted">
                                Select the sender name that will appear on recipients' phones.
                                <?php if (empty($senderNames)): ?>
                                    <span class="text-warning">Please contact your administrator to assign sender names to your account.</span>
                                <?php endif; ?>
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea class="form-control" name="message" rows="4" maxlength="160" required></textarea>
                            <div class="form-text">160 characters per SMS.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Schedule (Optional)</label>
                            <input class="form-control" type="datetime-local" name="scheduled_at">
                        </div>

                        <button type="submit" class="btn btn-primary">Launch Campaign</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.getElementById('recipientSource').addEventListener('change', function() {
        const source = this.value;
        document.getElementById('csvUpload').classList.add('d-none');
        document.getElementById('manualInput').classList.add('d-none');

        if (source === 'csv') {
            document.getElementById('csvUpload').classList.remove('d-none');
        } else if (source === 'manual') {
            document.getElementById('manualInput').classList.remove('d-none');
        }
    });
    feather.replace();
</script>
@endsection