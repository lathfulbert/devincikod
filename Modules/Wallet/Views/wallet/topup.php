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
                    <li class="breadcrumb-item"><a href="<?= route('home') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= route('wallet.index') ?>">Wallet</a></li>
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
                    <form action="<?= route('wallet.topup') ?>" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="user_id" value="<?= $userId ?>">

                        <div class="mb-3">
                            <label class="form-label">Amount (XOF)</label>
                            <div class="input-group">
                                <span class="input-group-text">XOF</span>
                                <input class="form-control" type="number" name="amount"
                                    min="100" max="10000000" step="1" required
                                    placeholder="Minimum 100 XOF">
                            </div>
                            <small class="text-muted">
                                Minimum: 100 XOF | Maximum: 10,000,000 XOF par recharge
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Méthode de paiement <span class="text-danger">*</span></label>
                            <select name="payment_method" id="payment_method" class="form-select" required>
                                <option value="">-- Sélectionner une méthode --</option>
                                <optgroup label="Paiement Offline (validation admin requise)">
                                    <option value="cash">Espèces / Cash</option>
                                    <option value="mobile_money" selected>Mobile Money (Orange, MTN, Moov, etc.)</option>
                                    <option value="bank_transfer">Virement bancaire</option>
                                    <option value="other">Autre méthode</option>
                                </optgroup>
                                <?php if (!empty($gateways)): ?>
                                    <optgroup label="Paiement en ligne (crédit automatique)">
                                        <?php foreach ($gateways as $gatewayInfo): ?>
                                            <option value="gateway" data-gateway-code="<?= htmlspecialchars($gatewayInfo['code']) ?>">
                                                <?= htmlspecialchars($gatewayInfo['name']) ?> - Paiement instantané
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endif; ?>
                            </select>
                            <input type="hidden" name="gateway_code" id="gateway_code">
                            <small class="text-muted">
                                <strong>Offline :</strong> Demande en attente de validation admin |
                                <strong>Gateway :</strong> Crédit automatique après paiement
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes / Référence de paiement (optionnel)</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3"
                                placeholder="Ex: Référence Orange Money, numéro de transaction, reçu bancaire, etc."></textarea>
                            <small class="text-muted">
                                Pour les paiements offline, indiquez toute information utile pour la validation
                                (référence de transaction, numéro de reçu, etc.).
                            </small>
                        </div>

                        <div class="alert alert-info" id="payment-info">
                            <i data-feather="info"></i>
                            <span id="payment-info-text">
                                <strong>Mobile Money sélectionné :</strong> Votre demande sera soumise pour validation par un administrateur.
                                Vous recevrez une notification une fois votre demande traitée.
                            </span>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i data-feather="send"></i> Soumettre la demande
                        </button>

                        <div class="mt-3 text-center">
                            <a href="<?= route('wallet.requests') ?>" class="btn btn-link">
                                <i data-feather="list"></i> Voir mes demandes précédentes
                            </a>
                        </div>
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
                            <a href="<?= route('sms.index') ?>" class="btn btn-secondary btn-sm">
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

    // Validation du montant de recharge
    const amountInput = document.querySelector('input[name="amount"]');
    const form = document.querySelector('form');
    const paymentMethodSelect = document.getElementById('payment_method');
    const gatewayCodeInput = document.getElementById('gateway_code');
    const paymentInfoDiv = document.getElementById('payment-info');
    const paymentInfoText = document.getElementById('payment-info-text');

    // Gestion du changement de méthode de paiement
    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const paymentMethod = this.value;
            const gatewayCode = selectedOption.getAttribute('data-gateway-code');

            // Mettre à jour le gateway_code si c'est une gateway
            if (gatewayCode) {
                gatewayCodeInput.value = gatewayCode;
            } else {
                gatewayCodeInput.value = '';
            }

            // Mettre à jour le message d'information
            if (paymentMethod === 'gateway') {
                paymentInfoDiv.className = 'alert alert-success';
                paymentInfoText.innerHTML = '<i data-feather="zap"></i> <strong>Paiement en ligne :</strong> Vous serez redirigé vers la passerelle de paiement. Votre wallet sera crédité automatiquement après paiement réussi.';
            } else if (paymentMethod === 'cash') {
                paymentInfoDiv.className = 'alert alert-warning';
                paymentInfoText.innerHTML = '<i data-feather="alert-circle"></i> <strong>Paiement en espèces :</strong> Votre demande sera soumise pour validation par un administrateur. Préparez votre preuve de paiement.';
            } else if (paymentMethod === 'mobile_money') {
                paymentInfoDiv.className = 'alert alert-info';
                paymentInfoText.innerHTML = '<i data-feather="smartphone"></i> <strong>Mobile Money :</strong> Effectuez d\'abord le paiement via votre application mobile, puis indiquez la référence de transaction dans les notes ci-dessus.';
            } else if (paymentMethod === 'bank_transfer') {
                paymentInfoDiv.className = 'alert alert-info';
                paymentInfoText.innerHTML = '<i data-feather="briefcase"></i> <strong>Virement bancaire :</strong> Effectuez le virement puis indiquez la référence dans les notes. L\'administrateur validera votre demande.';
            } else if (paymentMethod === 'other') {
                paymentInfoDiv.className = 'alert alert-secondary';
                paymentInfoText.innerHTML = '<i data-feather="help-circle"></i> <strong>Autre méthode :</strong> Précisez la méthode utilisée dans les notes ci-dessus.';
            } else {
                paymentInfoDiv.className = 'alert alert-info';
                paymentInfoText.innerHTML = '<i data-feather="info"></i> Sélectionnez une méthode de paiement pour continuer.';
            }

            // Re-render feather icons
            feather.replace();
        });

        // Trigger initial update
        paymentMethodSelect.dispatchEvent(new Event('change'));
    }

    if (amountInput && form) {
        form.addEventListener('submit', function(e) {
            const amount = parseFloat(amountInput.value);

            if (isNaN(amount) || amount < 100) {
                e.preventDefault();
                alert('Le montant minimum est de 100 XOF');
                amountInput.focus();
                return false;
            }

            if (amount > 10000000) {
                e.preventDefault();
                alert('Le montant maximum par recharge est de 10,000,000 XOF');
                amountInput.focus();
                return false;
            }

            // Vérification du solde résultant
            const currentBalance = <?= $wallet->balance ?? 0 ?>;
            const newBalance = currentBalance + amount;

            if (newBalance > 9999999999999.99) {
                e.preventDefault();
                alert('Cette recharge dépasserait la limite maximale du solde (9,999,999,999,999.99 XOF)');
                amountInput.focus();
                return false;
            }

            // Validation méthode de paiement
            if (!paymentMethodSelect.value) {
                e.preventDefault();
                alert('Veuillez sélectionner une méthode de paiement');
                paymentMethodSelect.focus();
                return false;
            }
        });

        // Validation en temps réel
        amountInput.addEventListener('input', function() {
            const amount = parseFloat(this.value);

            if (amount > 10000000) {
                this.setCustomValidity('Le montant maximum est de 10,000,000 XOF');
            } else if (amount < 100 && amount > 0) {
                this.setCustomValidity('Le montant minimum est de 100 XOF');
            } else {
                this.setCustomValidity('');
            }
        });
    }
</script>
@endsection