@extends('backend.layouts.master')

@section('title', 'Send SMS')

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
                    <li class="breadcrumb-item"><a href="<?= url('/admin/sms') ?>">SMS</a></li>
                    <li class="breadcrumb-item active">Send</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h5><i data-feather="send"></i> Send SMS</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= url('/admin/sms/send') ?>">
                        <?= csrf_field() ?>

                        <div class="form-group mb-3">
                            <label for="to">Recipient Phone Number</label>
                            <input type="text" class="form-control" id="to" name="to"
                                placeholder="+1234567890" required>
                            <small class="form-text text-muted">Include country code (e.g., +1 for US)</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="sender">Sender ID</label>
                            <input type="text" class="form-control" id="sender" name="sender"
                                value="SMS" maxlength="11">
                            <small class="form-text text-muted">Max 11 characters</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="message">Message</label>
                            <textarea class="form-control" id="message" name="message"
                                rows="4" maxlength="160" required></textarea>
                            <small class="form-text text-muted">
                                <span id="char-count">0</span>/160 characters
                            </small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="gateway">Gateway</label>
                            <select class="form-control" id="gateway" name="gateway">
                                <option value="auto">Auto (Best Available)</option>
                                <?php if (isset($gateways) && count($gateways) > 0): ?>
                                    <?php foreach ($gateways as $gw): ?>
                                        <option value="<?= htmlspecialchars($gw->provider_code) ?>">
                                            <?= htmlspecialchars($gw->name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="alert alert-info">
                            <i data-feather="info"></i>
                            Estimated Cost: <strong>$0.03</strong>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="send"></i> Send SMS
                            </button>
                            <a href="<?= url('/admin/sms') ?>" class="btn btn-secondary">Cancel</a>
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
    document.getElementById('message').addEventListener('input', function() {
        document.getElementById('char-count').textContent = this.value.length;
    });
    feather.replace();
</script>
@endsection