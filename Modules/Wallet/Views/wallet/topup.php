@extends('backend.layouts.master')

@section('title', 'Top-up Wallet')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $title ?? 'Top-up Wallet' ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/wallet') ?>">Wallet</a></li>
                    <li class="breadcrumb-item active">Top-up</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12 col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5>Add Funds</h5>
                </div>
                <div class="card-body">
                    <form action="<?= url('/admin/wallet/topup') ?>" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="user_id" value="<?= $userId ?>">

                        <div class="mb-3">
                            <label class="form-label">Amount (XOF)</label>
                            <div class="input-group">
                                <span class="input-group-text">XOF</span>
                                <input class="form-control" type="number" name="amount" min="100" step="1" required placeholder="Minimum 100 XOF">
                            </div>
                            <small class="text-muted">Minimum top-up: 100 XOF</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-check radio radio-primary">
                                        <input class="form-check-input" id="paypal" type="radio" name="method" value="paypal" checked>
                                        <label class="form-check-label" for="paypal">PayPal</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check radio radio-primary">
                                        <input class="form-check-input" id="stripe" type="radio" name="method" value="stripe">
                                        <label class="form-check-label" for="stripe">Credit Card (Stripe)</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Proceed to Payment</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5>Current Balance</h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <h1 class="display-4 text-primary"><?= number_format($wallet->balance ?? 0, 2) ?> XOF</h1>
                        <p class="text-muted">Available Credits</p>
                        <hr>
                        <ul class="list-unstyled text-start">
                            <li><i data-feather="check" class="text-success me-2"></i> Instant Crediting</li>
                            <li><i data-feather="check" class="text-success me-2"></i> Secure Transactions</li>
                            <li><i data-feather="check" class="text-success me-2"></i> Transaction History</li>
                        </ul>
                        <div class="mt-3">
                            <a href="<?= url('/admin/sms') ?>" class="btn btn-secondary btn-sm">
                                <i data-feather="arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
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