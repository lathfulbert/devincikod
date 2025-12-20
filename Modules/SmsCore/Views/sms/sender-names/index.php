@extends('backend.layouts.master')

@section('title', 'Sender Names Management')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $title ?? 'Sender Names Management' ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= route('home') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= route('sms.index') ?>">SMS</a></li>
                    <li class="breadcrumb-item active">Sender Names</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['flash_success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['flash_error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i data-feather="message-square"></i> Sender Names</h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="<?= route('sms.sender-names.create') ?>" class="btn btn-sm btn-primary">
                                <i data-feather="plus"></i> Add Sender Name
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Operator</th>
                                    <th>Status</th>
                                    <th>Active</th>
                                    <th>Validation Date</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($senderNames) || count($senderNames) === 0): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i data-feather="inbox" style="width: 48px; height: 48px;"></i>
                                            <p class="mt-2">No sender names yet. <a href="<?= url('/admin/sms/sender-names/create') ?>">Create one</a></p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($senderNames as $senderName): ?>
                                        <tr>
                                            <td><?= $senderName->id ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($senderName->name) ?></strong>
                                            </td>
                                            <td>
                                                <?php if ($senderName->operator): ?>
                                                    <span class="badge bg-info"><?= htmlspecialchars($senderName->operator) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($senderName->status === 'approved'): ?>
                                                    <span class="badge bg-success">Approved</span>
                                                <?php elseif ($senderName->status === 'pending'): ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php elseif ($senderName->status === 'rejected'): ?>
                                                    <span class="badge bg-danger">Rejected</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><?= ucfirst($senderName->status) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($senderName->is_active): ?>
                                                    <span class="badge bg-success"><i data-feather="check"></i> Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><i data-feather="x"></i> Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= $senderName->validation_date ? date('M d, Y', strtotime($senderName->validation_date)) : '<span class="text-muted">-</span>' ?>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($senderName->created_at)) ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?= url('/admin/sms/sender-names/assign-users?id=' . $senderName->id) ?>"
                                                       class="btn btn-sm btn-outline-info"
                                                       title="Assign to users">
                                                        <i data-feather="users"></i>
                                                    </a>
                                                    <a href="<?= url('/admin/sms/sender-names/edit?id=' . $senderName->id) ?>"
                                                       class="btn btn-sm btn-outline-primary"
                                                       title="Edit">
                                                        <i data-feather="edit"></i>
                                                    </a>
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-danger"
                                                            onclick="confirmDelete(<?= $senderName->id ?>, '<?= htmlspecialchars($senderName->name) ?>')"
                                                            title="Delete">
                                                        <i data-feather="trash-2"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5><i data-feather="info"></i> About Sender Names</h5>
                </div>
                <div class="card-body">
                    <p><strong>Sender Names</strong> are the identifiers that appear on recipients' phones when they receive an SMS.</p>
                    <ul>
                        <li><strong>Approved:</strong> Validated by the operator and ready to use</li>
                        <li><strong>Pending:</strong> Awaiting operator validation</li>
                        <li><strong>Rejected:</strong> Not approved by the operator</li>
                    </ul>
                    <p class="mb-0">
                        <i data-feather="alert-circle"></i>
                        <small>Before users can send SMS with a sender name, you must assign it to them using the <i data-feather="users"></i> button.</small>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<form id="deleteForm" method="POST" style="display: none;">
    <?= csrf_field() ?>
</form>

@endsection

@section('scripts')
<script>
    feather.replace();

    function confirmDelete(id, name) {
        if (confirm(`Are you sure you want to delete the sender name "${name}"?\n\nThis will also remove all user assignments for this sender name.`)) {
            const form = document.getElementById('deleteForm');
            form.action = '<?= url('/admin/sms/sender-names/delete') ?>';

            // Add hidden input for ID
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'id';
            input.value = id;
            form.appendChild(input);

            form.submit();
        }
    }
</script>
@endsection
