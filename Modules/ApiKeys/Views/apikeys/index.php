@extends('backend.layouts.master')

@section('title', $title ?? 'API Keys Management')

@section('content')

<div class="container-fluid">
    <?php
    // Breadcrumb
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'API Keys']
    ];
    component('breadcrumb', $breadcrumb);
    ?>
</div>

<div class="container-fluid">
    <!-- Messages Flash -->
    <?php component('alerts'); ?>

    <!-- Show new API key once (Modal) -->
    <?php if ($newKey): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <h5 class="alert-heading"><i data-feather="alert-triangle"></i> Important: Copy Your API Key Now!</h5>
            <p><strong>Name:</strong> <?= htmlspecialchars($newKey['name']) ?></p>
            <div class="input-group mb-3">
                <input type="text" class="form-control" id="newApiKey" value="<?= htmlspecialchars($newKey['key']) ?>" readonly>
                <button class="btn btn-primary" type="button" onclick="copyApiKey()">
                    <i data-feather="copy"></i> Copy
                </button>
            </div>
            <p class="mb-0"><small>This key will only be shown once. Make sure to copy it to a secure location.</small></p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
</div>

<div class="container-fluid">
    <!-- Actions -->
    <div class="row mb-3">
        <div class="col-12 text-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createApiKeyModal">
                <i data-feather="plus"></i> Generate New API Key
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <?php
            component('card-start', ['card_title' => "Your API Keys"]);
            ?>

            <table id="apiKeysTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Prefix</th>
                        <th>Key Preview</th>
                        <th>Last Used</th>
                        <th>Expires At</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($apiKeys)): ?>
                        <?php foreach ($apiKeys as $apiKey): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($apiKey->name) ?></strong></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($apiKey->prefix ?? 'sk_live') ?></span></td>
                                <td>
                                    <code><?= htmlspecialchars(substr($apiKey->key, 0, 15)) ?>...</code>
                                </td>
                                <td>
                                    <?php if ($apiKey->last_used_at): ?>
                                        <?= date('Y-m-d H:i', strtotime($apiKey->last_used_at)) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Never</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($apiKey->expires_at): ?>
                                        <?= date('Y-m-d', strtotime($apiKey->expires_at)) ?>
                                        <?php if (strtotime($apiKey->expires_at) < time()): ?>
                                            <span class="badge bg-danger">Expired</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Never</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($apiKey->deleted_at): ?>
                                        <span class="badge bg-danger">Révoquée</span>
                                    <?php elseif ($apiKey->is_active): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if (!$apiKey->deleted_at && $apiKey->is_active): ?>
                                        <form action="<?= url('/admin/system-api-keys/' . $apiKey->id . '/revoke') ?>"
                                            method="POST" style="display:inline;"
                                            onsubmit="return confirm('Revoke this API key?');">
                                            <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
                                            <button type="submit" class="btn btn-sm btn-warning" title="Revoke">
                                                <i data-feather="x-circle" style="width: 14px; height: 14px;"></i>
                                            </button>
                                        </form>
                                    <?php elseif (!$apiKey->deleted_at && !$apiKey->is_active): ?>
                                        <form action="<?= url('/admin/system-api-keys/' . $apiKey->id . '/activate') ?>"
                                            method="POST" style="display:inline;">
                                            <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
                                            <button type="submit" class="btn btn-sm btn-success" title="Activate">
                                                <i data-feather="check-circle" style="width: 14px; height: 14px;"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if (!$apiKey->deleted_at): ?>
                                        <form action="<?= url('/admin/system-api-keys/' . $apiKey->id . '/delete') ?>"
                                            method="POST" style="display:inline;"
                                            onsubmit="return confirm('Delete this API key permanently?');">
                                            <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                <i data-feather="inbox"></i> No API keys found. Generate your first one!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php
            $card_footer = "Total : " . count($apiKeys ?? []) . " API key(s)";
            component('card-end');
            ?>
        </div>
    </div>
</div>

<!-- Create API Key Modal -->
<div class="modal fade" id="createApiKeyModal" tabindex="-1" aria-labelledby="createApiKeyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= url('/admin/system-api-keys/store') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="modal-header">
                    <h5 class="modal-title" id="createApiKeyModalLabel">Generate New API Key</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control"
                            placeholder="e.g., Mobile App, Production API" required>
                        <small class="form-text text-muted">A descriptive name to identify this API key</small>
                    </div>

                    <div class="mb-3">
                        <label for="prefix" class="form-label">Prefix</label>
                        <select name="prefix" id="prefix" class="form-select">
                            <option value="sk_live">sk_live (Production)</option>
                            <option value="sk_test">sk_test (Testing)</option>
                            <option value="sk_dev">sk_dev (Development)</option>
                        </select>
                        <small class="form-text text-muted">Key prefix for identification</small>
                    </div>

                    <div class="mb-3">
                        <label for="ip_whitelist" class="form-label">IP Whitelist (Optional)</label>
                        <input type="text" name="ip_whitelist" id="ip_whitelist" class="form-control"
                            placeholder="e.g., 192.168.1.1, 10.0.0.1">
                        <small class="form-text text-muted">Comma-separated IPs. Leave empty to allow all IPs</small>
                    </div>

                    <div class="mb-3">
                        <label for="expires_at" class="form-label">Expiration Date (Optional)</label>
                        <input type="date" name="expires_at" id="expires_at" class="form-control">
                        <small class="form-text text-muted">Leave empty for no expiration</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="key"></i> Generate Key
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<?php
$datatable_id = 'apiKeysTable';
component('datatable-init');
?>
<script>
    feather.replace();

    function copyApiKey() {
        const input = document.getElementById('newApiKey');
        input.select();
        input.setSelectionRange(0, 99999); // For mobile devices

        navigator.clipboard.writeText(input.value).then(() => {
            // Show success feedback
            const btn = event.target;
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i data-feather="check"></i> Copied!';
            feather.replace();

            setTimeout(() => {
                btn.innerHTML = originalHTML;
                feather.replace();
            }, 2000);
        });
    }
</script>
@endsection