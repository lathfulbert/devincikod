@extends('backend.layouts.master')

@section('title', $title)

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
                    <li class="breadcrumb-item">SMS</li>
                    <li class="breadcrumb-item active">Wallet</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Solde Actuel</h6>
                            <h2 class="mt-2 mb-0">$<?= number_format($balance, 2) ?></h2>
                        </div>
                        <i data-feather="credit-card" style="width: 48px; height: 48px; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Effectuer une recharge</h5>
                </div>
                <div class="card-body">
                    <form action="<?= url('/admin/wallet/process-topup') ?>" method="POST">
                        <?= csrf_field() ?>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Montant à recharger ($)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" name="amount" min="5" step="0.01" required>
                                </div>
                                <small class="text-muted">Minimum $5.00</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Méthode de paiement</label>
                                <select class="form-select" name="payment_method">
                                    <option value="card">Carte Bancaire</option>
                                    <option value="paypal">PayPal</option>
                                    <option value="mobile_money">Mobile Money</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i data-feather="check-circle"></i> Procéder au paiement
                        </button>
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
</script>
@endsection