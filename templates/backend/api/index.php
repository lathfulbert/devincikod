@extends('backend.layouts.master')

@section('title', 'API Configuration')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>API Configuration</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/admin/dashboard') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item">Settings</li>
                    <li class="breadcrumb-item active">API</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php component('alerts'); ?>

    <div class="row">
        <!-- API Key Management -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i data-feather="key"></i> API Key</h5>
                </div>
                <div class="card-body">
                    <?php if ($hasApiKey): ?>
                        <div class="alert alert-success">
                            <i data-feather="check-circle"></i> API key is active
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Your API Key</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="apiKeyInput" value="<?= htmlspecialchars($user->api_key) ?>" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="toggleApiKey()">
                                    <i data-feather="eye" id="eyeIcon"></i>
                                </button>
                                <button class="btn btn-outline-primary" type="button" onclick="copyApiKey()">
                                    <i data-feather="copy"></i> Copy
                                </button>
                            </div>
                            <small class="text-muted">
                                Created: <?= $user->api_key_created_at ? date('d/m/Y H:i', strtotime($user->api_key_created_at)) : 'N/A' ?>
                            </small>
                        </div>

                        <div class="d-flex gap-2">
                            <form action="<?= url('/admin/settings/api/regenerate') ?>" method="POST" onsubmit="return confirm('Are you sure? This will invalidate your current API key.')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-warning">
                                    <i data-feather="refresh-cw"></i> Regenerate Key
                                </button>
                            </form>

                            <form action="<?= url('/admin/settings/api/revoke') ?>" method="POST" onsubmit="return confirm('Are you sure you want to revoke your API key?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger">
                                    <i data-feather="x-circle"></i> Revoke Key
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i data-feather="alert-circle"></i> No API key generated yet
                        </div>

                        <p>Generate an API key to integrate SMS functionality into your applications.</p>

                        <form action="<?= url('/admin/settings/api/generate') ?>" method="POST">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="plus"></i> Generate API Key
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- API Information -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i data-feather="info"></i> API Information</h5>
                </div>
                <div class="card-body">
                    <h6>Base URL</h6>
                    <div class="mb-3">
                        <code class="d-block p-2 bg-light rounded"><?= url('/api/v1') ?></code>
                    </div>

                    <h6>Authentication</h6>
                    <p>Include your API key in the Authorization header:</p>
                    <pre class="bg-light p-2 rounded"><code>Authorization: Bearer YOUR_API_KEY</code></pre>

                    <h6>Available Endpoints</h6>
                    <ul>
                        <li><strong>POST</strong> <code>/api/v1/sms/send</code> - Send SMS</li>
                        <li><strong>GET</strong> <code>/api/v1/sms/history</code> - Get SMS history</li>
                        <li><strong>GET</strong> <code>/api/v1/sms/balance</code> - Get account balance</li>
                    </ul>

                    <a href="<?= url('/admin/settings/api/docs') ?>" class="btn btn-info">
                        <i data-feather="book"></i> View Full Documentation
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function toggleApiKey() {
    const input = document.getElementById('apiKeyInput');
    const icon = document.getElementById('eyeIcon');

    if (input.type === 'password') {
        input.type = 'text';
        icon.setAttribute('data-feather', 'eye-off');
    } else {
        input.type = 'password';
        icon.setAttribute('data-feather', 'eye');
    }
    feather.replace();
}

function copyApiKey() {
    const input = document.getElementById('apiKeyInput');
    const originalType = input.type;

    input.type = 'text';
    input.select();
    document.execCommand('copy');
    input.type = originalType;

    // Show success message
    alert('API key copied to clipboard!');
}

feather.replace();
</script>
@endsection
