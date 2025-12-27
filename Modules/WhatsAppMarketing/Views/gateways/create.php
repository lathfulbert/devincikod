@extends('backend.layouts.master')

@section('title', $title)

@section('content')

<div class="container-fluid">
    <?php
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'WhatsApp', 'url' => '/admin/whatsapp'],
        ['label' => 'Passerelles', 'url' => '/admin/whatsapp/gateways'],
        ['label' => 'Ajouter']
    ];
    component('breadcrumb');
    ?>

    <?php component('alerts'); ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Nouvelle Passerelle</h6>
        </div>
        <div class="card-body">
            <form action="<?= url('/admin/whatsapp/gateways/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Nom de la passerelle</label>
                    <input type="text" name="name" class="form-control" required placeholder="Ex: Twilio Marketing">
                </div>

                <div class="mb-3">
                    <label class="form-label">Fournisseur</label>
                    <select name="provider" class="form-control" id="provider_select" required>
                        <option value="twilio">Twilio</option>
                        <option value="wati">WATI</option>
                        <option value="mock">Mock (Test)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Numéro de téléphone (Sender)</label>
                    <input type="text" name="phone_number" class="form-control" placeholder="+1234567890">
                </div>

                <!-- Twilio Fields -->
                <div id="twilio_fields">
                    <div class="mb-3">
                        <label class="form-label">Account SID</label>
                        <input type="text" name="twilio_sid" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Auth Token</label>
                        <input type="password" name="twilio_token" class="form-control">
                    </div>
                </div>

                <!-- Generic/Wati Fields -->
                <div id="generic_fields" style="display:none;">
                    <div class="mb-3">
                        <label class="form-label">API Key / Access Token</label>
                        <input type="password" name="api_key" class="form-control">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    feather.replace();

    const providerSelect = document.getElementById('provider_select');
    const twilioFields = document.getElementById('twilio_fields');
    const genericFields = document.getElementById('generic_fields');

    providerSelect.addEventListener('change', function() {
        if (this.value === 'twilio') {
            twilioFields.style.display = 'block';
            genericFields.style.display = 'none';
        } else {
            twilioFields.style.display = 'none';
            genericFields.style.display = 'block';
        }
    });
</script>
@endsection