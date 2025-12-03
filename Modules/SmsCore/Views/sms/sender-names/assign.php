@extends('backend.layouts.master')

@section('title', 'Assign Users')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $title ?? 'Assign Users to Sender Name' ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/sms') ?>">SMS</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/sms/sender-names') ?>">Sender Names</a></li>
                    <li class="breadcrumb-item active">Assign Users</li>
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
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>
                                <i data-feather="users"></i>
                                Assign Users to <strong><?= htmlspecialchars($senderName->name ?? '') ?></strong>
                            </h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="<?= url('/sms/sender-names') ?>" class="btn btn-sm btn-secondary">
                                <i data-feather="arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($senderName->operator): ?>
                        <div class="alert alert-info mb-3">
                            <strong>Operator:</strong> <?= htmlspecialchars($senderName->operator) ?>
                            <span class="ms-3">
                                <strong>Status:</strong>
                                <?php if ($senderName->status === 'approved'): ?>
                                    <span class="badge bg-success">Approved</span>
                                <?php else: ?>
                                    <span class="badge bg-warning"><?= ucfirst($senderName->status) ?></span>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= url('/sms/sender-names/save-assignments') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="sender_name_id" value="<?= $senderName->id ?>">

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">
                                    <strong>Select Users</strong>
                                    <small class="text-muted">(<?= count($users ?? []) ?> total users)</small>
                                </label>
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                                        Select All
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">
                                        Deselect All
                                    </button>
                                </div>
                            </div>

                            <div class="alert alert-light">
                                <strong><span id="selectedCount">0</span></strong> user(s) selected
                            </div>

                            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-hover table-sm">
                                    <thead class="table-light" style="position: sticky; top: 0;">
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" id="selectAllCheckbox" onchange="toggleAll(this)">
                                            </th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Name</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($users)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">
                                                    No active users found
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($users as $user): ?>
                                                <?php
                                                    $isAssigned = in_array($user->id, $assignedUserIds ?? []);
                                                    $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                                                ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox"
                                                               name="user_ids[]"
                                                               value="<?= $user->id ?>"
                                                               class="user-checkbox"
                                                               <?= $isAssigned ? 'checked' : '' ?>
                                                               onchange="updateCount()">
                                                    </td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($user->username ?? '') ?></strong>
                                                    </td>
                                                    <td><?= htmlspecialchars($user->email ?? '') ?></td>
                                                    <td><?= $fullName ? htmlspecialchars($fullName) : '<span class="text-muted">-</span>' ?></td>
                                                    <td>
                                                        <?php if ($isAssigned): ?>
                                                            <span class="badge bg-success">Currently Assigned</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Not Assigned</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="save"></i> Save Assignments
                            </button>
                            <a href="<?= url('/sms/sender-names') ?>" class="btn btn-secondary">
                                <i data-feather="x"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    feather.replace();

    // Count selected users
    function updateCount() {
        const checked = document.querySelectorAll('.user-checkbox:checked').length;
        document.getElementById('selectedCount').textContent = checked;

        const total = document.querySelectorAll('.user-checkbox').length;
        document.getElementById('selectAllCheckbox').checked = (checked === total && total > 0);
    }

    // Toggle all checkboxes
    function toggleAll(checkbox) {
        document.querySelectorAll('.user-checkbox').forEach(cb => {
            cb.checked = checkbox.checked;
        });
        updateCount();
    }

    // Select all users
    function selectAll() {
        document.querySelectorAll('.user-checkbox').forEach(cb => {
            cb.checked = true;
        });
        document.getElementById('selectAllCheckbox').checked = true;
        updateCount();
    }

    // Deselect all users
    function deselectAll() {
        document.querySelectorAll('.user-checkbox').forEach(cb => {
            cb.checked = false;
        });
        document.getElementById('selectAllCheckbox').checked = false;
        updateCount();
    }

    // Initialize count on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateCount();
    });
</script>
@endsection
