@extends('backend.layouts.master')

@section('title', 'Configuration Tarifaire SMS')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Tarification SMS</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/admin/dashboard') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item">SMS</li>
                    <li class="breadcrumb-item active">Tarification</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php component('alerts'); ?>

    <!-- Tarif par défaut -->
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary">
                    <h5 class="text-white mb-0"><i data-feather="globe"></i> Tarif Global par Défaut</h5>
                </div>
                <div class="card-body">
                    <form action="<?= url('/admin/sms/pricing/update-default') ?>" method="POST">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Prix par SMS</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" name="default_price"
                                       value="<?= $defaultPrice ?? 15 ?>" required>
                                <span class="input-group-text">XOF</span>
                            </div>
                            <small class="text-muted">Appliqué si aucun tarif spécifique n'est défini</small>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i data-feather="save"></i> Mettre à jour
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i data-feather="map-pin"></i> Tarifs par Pays</h5>
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addCountryModal">
                        <i data-feather="plus"></i> Ajouter un pays
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Pays</th>
                                    <th>Code</th>
                                    <th>Prix par défaut</th>
                                    <th>Opérateurs</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="countriesTable">
                                <?php foreach ($countries as $code => $data): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($data['name'] ?? $code) ?></strong></td>
                                        <td><span class="badge badge-secondary"><?= $code ?></span></td>
                                        <td><strong><?= number_format($data['default'] ?? 0, 2) ?> XOF</strong></td>
                                        <td>
                                            <?php if (!empty($data['networks'])): ?>
                                                <span class="badge badge-info"><?= count($data['networks']) ?> opérateurs</span>
                                            <?php else: ?>
                                                <span class="text-muted">Aucun</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" onclick="editCountry('<?= $code ?>')">
                                                <i data-feather="edit-2"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteCountry('<?= $code ?>')">
                                                <i data-feather="trash-2"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($countries)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Aucun tarif spécifique configuré
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajouter/Éditer Pays -->
<div class="modal fade" id="addCountryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Ajouter un Pays</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url('/admin/sms/pricing/update-country') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" name="country_code" id="country_code">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pays *</label>
                            <select class="form-select" name="country" id="country" required>
                                <option value="">Sélectionner...</option>
                                <option value="CI">🇨🇮 Côte d'Ivoire (CI)</option>
                                <option value="SN">🇸🇳 Sénégal (SN)</option>
                                <option value="ML">🇲🇱 Mali (ML)</option>
                                <option value="BF">🇧🇫 Burkina Faso (BF)</option>
                                <option value="BJ">🇧🇯 Bénin (BJ)</option>
                                <option value="TG">🇹🇬 Togo (TG)</option>
                                <option value="NE">🇳🇪 Niger (NE)</option>
                                <option value="GN">🇬🇳 Guinée (GN)</option>
                                <option value="CM">🇨🇲 Cameroun (CM)</option>
                                <option value="FR">🇫🇷 France (FR)</option>
                                <option value="US">🇺🇸 États-Unis (US)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prix par défaut *</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" name="default_price"
                                       id="default_price" required>
                                <span class="input-group-text">XOF</span>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h6 class="mb-3">Tarifs par Opérateur (optionnel)</h6>
                    <div id="operatorsContainer">
                        <div class="row operator-row mb-2">
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="operators[0][name]" placeholder="Ex: Orange, MTN, Moov">
                            </div>
                            <div class="col-md-5">
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" name="operators[0][price]" placeholder="Prix">
                                    <span class="input-group-text">XOF</span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-sm btn-danger" onclick="removeOperator(this)">
                                    <i data-feather="x"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="addOperator()">
                        <i data-feather="plus"></i> Ajouter un opérateur
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
let operatorIndex = 1;
let countriesData = <?= json_encode($countries ?? []) ?>;

function addOperator() {
    const container = document.getElementById('operatorsContainer');
    const html = `
        <div class="row operator-row mb-2">
            <div class="col-md-5">
                <input type="text" class="form-control" name="operators[${operatorIndex}][name]" placeholder="Ex: Orange, MTN, Moov">
            </div>
            <div class="col-md-5">
                <div class="input-group">
                    <input type="number" step="0.01" class="form-control" name="operators[${operatorIndex}][price]" placeholder="Prix">
                    <span class="input-group-text">XOF</span>
                </div>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-danger" onclick="removeOperator(this)">
                    <i data-feather="x"></i>
                </button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    operatorIndex++;
    feather.replace();
}

function removeOperator(btn) {
    btn.closest('.operator-row').remove();
}

function editCountry(code) {
    const country = countriesData[code];
    if (!country) return;

    document.getElementById('modalTitle').textContent = 'Éditer le Pays';
    document.getElementById('country_code').value = code;
    document.getElementById('country').value = code;
    document.getElementById('default_price').value = country.default || 0;

    // Clear operators
    document.getElementById('operatorsContainer').innerHTML = '';
    operatorIndex = 0;

    // Add existing operators
    if (country.networks) {
        for (const [name, price] of Object.entries(country.networks)) {
            const container = document.getElementById('operatorsContainer');
            const html = `
                <div class="row operator-row mb-2">
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="operators[${operatorIndex}][name]" value="${name}">
                    </div>
                    <div class="col-md-5">
                        <div class="input-group">
                            <input type="number" step="0.01" class="form-control" name="operators[${operatorIndex}][price]" value="${price}">
                            <span class="input-group-text">XOF</span>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeOperator(this)">
                            <i data-feather="x"></i>
                        </button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            operatorIndex++;
        }
    }

    const modal = new bootstrap.Modal(document.getElementById('addCountryModal'));
    modal.show();

    feather.replace();
}

function deleteCountry(code) {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer les tarifs pour ${code}?`)) {
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= url('/admin/sms/pricing/delete-country') ?>';

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_csrf';
    csrf.value = '<?= csrf_token() ?>';

    const codeInput = document.createElement('input');
    codeInput.type = 'hidden';
    codeInput.name = 'country_code';
    codeInput.value = code;

    form.appendChild(csrf);
    form.appendChild(codeInput);
    document.body.appendChild(form);
    form.submit();
}

// Reset modal on close
document.getElementById('addCountryModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('modalTitle').textContent = 'Ajouter un Pays';
    document.getElementById('country_code').value = '';
    document.getElementById('country').value = '';
    document.getElementById('default_price').value = '';
    document.getElementById('operatorsContainer').innerHTML = `
        <div class="row operator-row mb-2">
            <div class="col-md-5">
                <input type="text" class="form-control" name="operators[0][name]" placeholder="Ex: Orange, MTN, Moov">
            </div>
            <div class="col-md-5">
                <div class="input-group">
                    <input type="number" step="0.01" class="form-control" name="operators[0][price]" placeholder="Prix">
                    <span class="input-group-text">XOF</span>
                </div>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-danger" onclick="removeOperator(this)">
                    <i data-feather="x"></i>
                </button>
            </div>
        </div>
    `;
    operatorIndex = 1;
});

feather.replace();
</script>
@endsection
