@extends('backend.layouts.master')

@section('title', $title ?? 'Configuration Mail')

@section('content')

<div class="container-fluid">
    <?php
    // Breadcrumb
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'Configuration', 'url' => '/admin/settings'],
        ['label' => 'Configuration Mail']
    ];
    component('breadcrumb');
    ?>
</div>

<div class="container-fluid">
    <!-- Messages Flash -->
    <?php component('alerts'); ?>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <?php
            component('card-start', ['card_title' => "Configuration Mail"]);
            ?>

            <form action="<?= url('/admin/settings/mail/update') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="mail_driver" class="form-label">Driver Mail</label>
                        <select name="mail_driver" id="mail_driver" class="form-select">
                            <option value="smtp" <?= ($settings['mail_driver'] ?? 'smtp') === 'smtp' ? 'selected' : '' ?>>SMTP</option>
                            <option value="sendmail" <?= ($settings['mail_driver'] ?? 'smtp') === 'sendmail' ? 'selected' : '' ?>>Sendmail</option>
                            <option value="mailgun" <?= ($settings['mail_driver'] ?? 'smtp') === 'mailgun' ? 'selected' : '' ?>>Mailgun</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="mail_from_address" class="form-label">Adresse Email</label>
                        <input type="email" name="mail_from_address" id="mail_from_address" class="form-control"
                               value="<?= htmlspecialchars($settings['mail_from_address'] ?? '') ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="mail_from_name" class="form-label">Nom Expéditeur</label>
                        <input type="text" name="mail_from_name" id="mail_from_name" class="form-control"
                               value="<?= htmlspecialchars($settings['mail_from_name'] ?? '') ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="mail_host" class="form-label">Serveur SMTP</label>
                        <input type="text" name="mail_host" id="mail_host" class="form-control"
                               value="<?= htmlspecialchars($settings['mail_host'] ?? '') ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="mail_port" class="form-label">Port SMTP</label>
                        <input type="number" name="mail_port" id="mail_port" class="form-control"
                               value="<?= htmlspecialchars($settings['mail_port'] ?? '587') ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="mail_encryption" class="form-label">Encryption</label>
                        <select name="mail_encryption" id="mail_encryption" class="form-select">
                            <option value="tls" <?= ($settings['mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option>
                            <option value="ssl" <?= ($settings['mail_encryption'] ?? 'tls') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                            <option value="" <?= empty($settings['mail_encryption']) ? 'selected' : '' ?>>Aucune</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="mail_username" class="form-label">Nom d'utilisateur</label>
                        <input type="text" name="mail_username" id="mail_username" class="form-control"
                               value="<?= htmlspecialchars($settings['mail_username'] ?? '') ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="mail_password" class="form-label">Mot de passe</label>
                        <input type="password" name="mail_password" id="mail_password" class="form-control"
                               placeholder="Laisser vide pour ne pas modifier">
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save"></i> Enregistrer
                    </button>
                    <a href="<?= url('/admin/settings') ?>" class="btn btn-secondary">
                        <i data-feather="x"></i> Annuler
                    </a>
                    <button type="button" class="btn btn-info" onclick="testMail()">
                        <i data-feather="send"></i> Tester l'envoi
                    </button>
                </div>
            </form>

            <?php component('card-end'); ?>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function testMail() {
    if (!confirm('Envoyer un email de test ?')) return;

    fetch('<?= url("/admin/settings/mail/test") ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('input[name="_csrf_token"]').value
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Email de test envoyé avec succès !');
        } else {
            alert('Erreur lors de l\'envoi : ' + (data.message || 'Erreur inconnue'));
        }
    })
    .catch(error => {
        alert('Erreur : ' + error.message);
    });
}
</script>
@endsection
