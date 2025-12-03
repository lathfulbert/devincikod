<!-- Sender Names List View -->
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Sender Names Management</h4>
                    <a href="/sms/sender-names/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Sender Name
                    </a>
                </div>

                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?= htmlspecialchars($_SESSION['success']) ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?= htmlspecialchars($_SESSION['error']) ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Sender Name</th>
                                    <th>Operator</th>
                                    <th>Status</th>
                                    <th>Active</th>
                                    <th>Validation Date</th>
                                    <th>Assigned Users</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($senderNames)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No sender names found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($senderNames as $senderName): ?>
                                        <tr>
                                            <td><?= $senderName->id ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($senderName->name) ?></strong>
                                            </td>
                                            <td><?= htmlspecialchars($senderName->operator ?? 'N/A') ?></td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'approved' => 'success',
                                                    'pending' => 'warning',
                                                    'rejected' => 'danger'
                                                ][$senderName->status] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?= $statusClass ?>">
                                                    <?= ucfirst($senderName->status) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $senderName->is_active ? 'success' : 'secondary' ?>">
                                                    <?= $senderName->is_active ? 'Active' : 'Inactive' ?>
                                                </span>
                                            </td>
                                            <td><?= $senderName->validation_date ?? 'N/A' ?></td>
                                            <td>
                                                <?php
                                                $assignedUsers = $senderName->getAssignedUsers();
                                                echo count($assignedUsers);
                                                ?> users
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="/sms/sender-names/assign-users?id=<?= $senderName->id ?>"
                                                       class="btn btn-info" title="Assign Users">
                                                        <i class="fas fa-users"></i>
                                                    </a>
                                                    <a href="/sms/sender-names/edit?id=<?= $senderName->id ?>"
                                                       class="btn btn-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-danger"
                                                            onclick="confirmDelete(<?= $senderName->id ?>, '<?= htmlspecialchars($senderName->name) ?>')"
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
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
</div>

<!-- Delete Confirmation Modal -->
<form id="deleteForm" method="POST" action="/sms/sender-names/delete">
    <input type="hidden" name="id" id="deleteId">
</form>

<script>
function confirmDelete(id, name) {
    if (confirm(`Are you sure you want to delete sender name "${name}"?\n\nThis will remove all user assignments.`)) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>
