@extends('backend.layouts.master')

@section('title', 'SMS History')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $title ?? 'SMS History' ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/sms') ?>">SMS</a></li>
                    <li class="breadcrumb-item active">History</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>SMS History</h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="<?= url('/admin/sms/send') ?>" class="btn btn-sm btn-primary">
                                <i data-feather="send"></i> Send New SMS
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Recipient</th>
                                    <th>Message</th>
                                    <th>Gateway</th>
                                    <th>Status</th>
                                    <th>Cost</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($messages as $msg): ?>
                                    <tr>
                                        <td><?= $msg['id'] ?></td>
                                        <td><?= htmlspecialchars($msg['to']) ?></td>
                                        <td><?= htmlspecialchars(substr($msg['message'], 0, 50)) ?>...</td>
                                        <td><span class="badge badge-info"><?= $msg['gateway'] ?></span></td>
                                        <td>
                                            <?php if ($msg['status'] === 'delivered'): ?>
                                                <span class="badge badge-success">Delivered</span>
                                            <?php elseif ($msg['status'] === 'sent'): ?>
                                                <span class="badge badge-warning">Sent</span>
                                            <?php elseif ($msg['status'] === 'failed'): ?>
                                                <span class="badge badge-danger">Failed</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary"><?= ucfirst($msg['status']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>$<?= number_format($msg['cost'], 2) ?></td>
                                        <td><?= date('M d, H:i', strtotime($msg['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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