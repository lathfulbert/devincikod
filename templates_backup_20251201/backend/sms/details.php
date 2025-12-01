@extends('backend.layouts.master')

@section('title', 'SMS Details')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>SMS Details</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/sms') ?>">SMS</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/sms/history') ?>">History</a></li>
                    <li class="breadcrumb-item active">Details</li>
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
                    <h5><i data-feather="message-square"></i> Message #<?= $sms->id ?></h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Status:</strong></div>
                        <div class="col-md-9">
                            <?php if ($sms->status === 'sent'): ?>
                                <span class="badge badge-success">Sent</span>
                            <?php elseif ($sms->status === 'failed'): ?>
                                <span class="badge badge-danger">Failed</span>
                            <?php elseif ($sms->status === 'delivered'): ?>
                                <span class="badge badge-info">Delivered</span>
                            <?php else: ?>
                                <span class="badge badge-warning"><?= ucfirst($sms->status) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Gateway:</strong></div>
                        <div class="col-md-9"><?= htmlspecialchars($sms->gateway) ?></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3"><strong>To:</strong></div>
                        <div class="col-md-9"><?= htmlspecialchars($sms->to) ?></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3"><strong>From:</strong></div>
                        <div class="col-md-9"><?= htmlspecialchars($sms->from) ?></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Message:</strong></div>
                        <div class="col-md-9"><?= nl2br(htmlspecialchars($sms->message)) ?></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Message ID:</strong></div>
                        <div class="col-md-9"><code><?= htmlspecialchars($sms->message_id) ?></code></div>
                    </div>

                    <?php if ($sms->gateway_message_id): ?>
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Gateway Message ID:</strong></div>
                        <div class="col-md-9"><code><?= htmlspecialchars($sms->gateway_message_id) ?></code></div>
                    </div>
                    <?php endif; ?>

                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Created At:</strong></div>
                        <div class="col-md-9"><?= date('Y-m-d H:i:s', strtotime($sms->created_at)) ?></div>
                    </div>

                    <?php if ($sms->sent_at): ?>
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Sent At:</strong></div>
                        <div class="col-md-9"><?= date('Y-m-d H:i:s', strtotime($sms->sent_at)) ?></div>
                    </div>
                    <?php endif; ?>

                    <?php if ($sms->error): ?>
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Error:</strong></div>
                        <div class="col-md-9"><div class="alert alert-danger"><?= htmlspecialchars($sms->error) ?></div></div>
                    </div>
                    <?php endif; ?>

                    <?php if ($sms->gateway_response): ?>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h6 class="mt-4"><strong>Gateway Response:</strong></h6>
                            <pre class="bg-light p-3 rounded"><code><?= json_encode($sms->gateway_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?></code></pre>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="mt-4">
                        <a href="<?= url('/admin/sms/history') ?>" class="btn btn-secondary">
                            <i data-feather="arrow-left"></i> Back to History
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    feather.replace();
</script>
@endsection
