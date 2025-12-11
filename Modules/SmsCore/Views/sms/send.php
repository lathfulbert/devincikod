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
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5><i data-feather="send"></i> Envoyer SMS</h5>
                    <p class="text-muted">Choisissez votre mode d'envoi</p>
                </div>
                <div class="card-body">
                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs border-tab nav-primary" id="smsTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="single-tab" data-bs-toggle="tab" href="#single" role="tab">
                                <i data-feather="smartphone"></i> SMS Unique
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="manual-tab" data-bs-toggle="tab" href="#manual" role="tab">
                                <i data-feather="edit"></i> Saisie Manuelle
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="file-tab" data-bs-toggle="tab" href="#file" role="tab">
                                <i data-feather="upload"></i> Import Fichier
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="contacts-tab" data-bs-toggle="tab" href="#contacts" role="tab">
                                <i data-feather="users"></i> Depuis Contacts
                            </a>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="smsTabContent">
                        <!-- Tab 1: Single SMS -->
                        <div class="tab-pane fade show active" id="single" role="tabpanel">
                            <form method="POST" action="<?= url('/admin/sms/send') ?>" class="mt-4">
                                <?= csrf_field() ?>
                                <input type="hidden" name="send_type" value="single">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="to">Numéro du Destinataire <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="to" name="to"
                                                placeholder="Ex: 0708090102 ou +2250708090102" required>
                                            <small class="form-text text-muted">
                                                <i data-feather="info" class="feather-sm"></i>
                                                Le préfixe <?= \Modules\Settings\Models\Setting::get('sms_default_country_code', '+225') ?> sera ajouté automatiquement
                                            </small>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="sender_name_id">Sender Name <span class="text-danger">*</span></label>
                                            <select class="form-control" id="sender_name_id" name="sender_name_id" required>
                                                <option value="">-- Sélectionner un Sender Name --</option>
                                                <?php if (isset($senderNames) && !empty($senderNames)): ?>
                                                    <?php foreach ($senderNames as $senderName): ?>
                                                        <option value="<?= $senderName->id ?>">
                                                            <?= htmlspecialchars($senderName->name) ?>
                                                            <?php if ($senderName->operator): ?>
                                                                (<?= htmlspecialchars($senderName->operator) ?>)
                                                            <?php endif; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <option value="" disabled>Aucun sender name assigné</option>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="message">Message <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="message" name="message"
                                        rows="4" maxlength="160" required></textarea>
                                    <small class="form-text text-muted">
                                        <span id="char-count">0</span>/160 caractères
                                    </small>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="gateway">Gateway</label>
                                    <select class="form-control" id="gateway" name="gateway">
                                        <option value="auto">Auto (Meilleur disponible)</option>
                                        <?php if (isset($gateways) && count($gateways) > 0): ?>
                                            <?php foreach ($gateways as $gw): ?>
                                                <option value="<?= htmlspecialchars($gw->provider_code) ?>">
                                                    <?= htmlspecialchars($gw->name) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="alert alert-light-info">
                                    <i data-feather="info"></i>
                                    Coût estimé: <strong class="text-primary">$0.03</strong>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i data-feather="send"></i> Envoyer SMS
                                    </button>
                                    <a href="<?= url('/admin/sms') ?>" class="btn btn-light">Annuler</a>
                                </div>
                            </form>
                        </div>

                        <!-- Tab 2: Manual Entry -->
                        <div class="tab-pane fade" id="manual" role="tabpanel">
                            <form method="POST" action="<?= url('/admin/sms/send-bulk') ?>" class="mt-4">
                                <?= csrf_field() ?>
                                <input type="hidden" name="send_type" value="manual">

                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group mb-3">
                                            <label for="recipients_manual">Numéros de Téléphone <span class="text-danger">*</span></label>
                                            <textarea class="form-control" id="recipients_manual" name="recipients_manual"
                                                rows="8" placeholder="Entrez les numéros (un par ligne, séparés par virgule ou point-virgule)
Exemple:
0708090102
0709101112
+221771234567" required></textarea>
                                            <small class="form-text text-muted">
                                                <i data-feather="info" class="feather-sm"></i>
                                                Formats acceptés: un numéro par ligne, ou séparés par , ou ;
                                            </small>
                                        </div>

                                        <div class="alert alert-light-info">
                                            <i data-feather="users"></i>
                                            Destinataires détectés: <strong id="manual-count" class="text-primary">0</strong>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6 class="mb-3">Configuration</h6>

                                                <div class="form-group mb-3">
                                                    <label for="sender_name_id_manual">Sender Name <span class="text-danger">*</span></label>
                                                    <select class="form-control" name="sender_name_id" required>
                                                        <option value="">-- Sélectionner --</option>
                                                        <?php if (isset($senderNames) && !empty($senderNames)): ?>
                                                            <?php foreach ($senderNames as $senderName): ?>
                                                                <option value="<?= $senderName->id ?>">
                                                                    <?= htmlspecialchars($senderName->name) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </select>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label for="campaign_name_manual">Nom de la campagne</label>
                                                    <input type="text" class="form-control" name="campaign_name"
                                                        value="Campagne <?= date('d/m/Y H:i') ?>">
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label for="scheduled_at">Programmer l'envoi <span class="text-muted">(optionnel)</span></label>
                                                    <input type="datetime-local" class="form-control" name="scheduled_at">
                                                    <small class="form-text text-muted">Laissez vide pour envoi immédiat</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="message_manual">Message <span class="text-danger">*</span></label>
                                    <textarea class="form-control message-field" id="message_manual" name="message"
                                        rows="4" maxlength="160" required></textarea>
                                    <small class="form-text text-muted">
                                        <span class="char-counter">0</span>/160 caractères
                                    </small>
                                </div>

                                <div class="alert alert-light-warning">
                                    <i data-feather="alert-circle"></i>
                                    Coût estimé: <strong id="manual-cost" class="text-warning">$0.00</strong>
                                    (<span id="manual-count-display">0</span> SMS × $0.03)
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i data-feather="send"></i> Envoyer à <span id="manual-count-btn">0</span> destinataires
                                    </button>
                                    <button type="button" class="btn btn-light" onclick="clearManualRecipients()">
                                        <i data-feather="x"></i> Effacer
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Tab 3: File Import -->
                        <div class="tab-pane fade" id="file" role="tabpanel">
                            <form method="POST" action="<?= url('/admin/sms/send-bulk') ?>" enctype="multipart/form-data" class="mt-4">
                                <?= csrf_field() ?>
                                <input type="hidden" name="send_type" value="file">

                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group mb-3">
                                            <label for="recipients_file">Fichier CSV/Excel <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control" id="recipients_file" name="recipients_file"
                                                accept=".csv,.xlsx,.xls" required>
                                            <small class="form-text text-muted">
                                                <i data-feather="file-text" class="feather-sm"></i>
                                                Formats acceptés: CSV, Excel (.xlsx, .xls)
                                            </small>
                                        </div>

                                        <div class="card border-primary mb-3">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0"><i data-feather="book-open"></i> Format du fichier</h6>
                                            </div>
                                            <div class="card-body">
                                                <p class="mb-2"><strong>Le fichier doit contenir:</strong></p>
                                                <ul class="mb-2">
                                                    <li>Première colonne: Numéros de téléphone</li>
                                                    <li>Optionnel: Deuxième colonne pour le nom du contact</li>
                                                    <li>La première ligne peut contenir des en-têtes (ignorés automatiquement)</li>
                                                </ul>

                                                <p class="mb-1"><strong>Exemple CSV:</strong></p>
                                                <pre class="bg-light p-2 rounded"><code>Téléphone,Nom
0708090102,Jean Dupont
0709101112,Marie Martin
+221771234567,Amadou Diallo</code></pre>

                                                <a href="<?= url('/admin/sms/download-template') ?>" class="btn btn-sm btn-outline-primary mt-2">
                                                    <i data-feather="download"></i> Télécharger un modèle
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6 class="mb-3">Configuration</h6>

                                                <div class="form-group mb-3">
                                                    <label for="sender_name_id_file">Sender Name <span class="text-danger">*</span></label>
                                                    <select class="form-control" name="sender_name_id" required>
                                                        <option value="">-- Sélectionner --</option>
                                                        <?php if (isset($senderNames) && !empty($senderNames)): ?>
                                                            <?php foreach ($senderNames as $senderName): ?>
                                                                <option value="<?= $senderName->id ?>">
                                                                    <?= htmlspecialchars($senderName->name) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </select>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label for="campaign_name_file">Nom de la campagne</label>
                                                    <input type="text" class="form-control" name="campaign_name"
                                                        value="Import <?= date('d/m/Y H:i') ?>">
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label for="scheduled_at">Programmer l'envoi <span class="text-muted">(optionnel)</span></label>
                                                    <input type="datetime-local" class="form-control" name="scheduled_at">
                                                    <small class="form-text text-muted">Laissez vide pour envoi immédiat</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="message_file">Message <span class="text-danger">*</span></label>
                                    <textarea class="form-control message-field" id="message_file" name="message"
                                        rows="4" maxlength="160" required></textarea>
                                    <small class="form-text text-muted">
                                        <span class="char-counter">0</span>/160 caractères
                                    </small>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i data-feather="upload"></i> Importer et Envoyer
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Tab 4: From Contacts -->
                        <div class="tab-pane fade" id="contacts" role="tabpanel">
                            <form method="POST" action="<?= url('/admin/sms/send-bulk') ?>" class="mt-4">
                                <?= csrf_field() ?>
                                <input type="hidden" name="send_type" value="contacts">

                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group mb-3">
                                            <label>Sélectionner les Contacts <span class="text-danger">*</span></label>

                                            <div class="input-group mb-3">
                                                <input type="text" class="form-control" id="contact-search"
                                                    placeholder="Rechercher un contact...">
                                                <button class="btn btn-outline-secondary" type="button" id="select-all-contacts">
                                                    <i data-feather="check-square"></i> Tout sélectionner
                                                </button>
                                                <button class="btn btn-outline-secondary" type="button" id="unselect-all-contacts">
                                                    <i data-feather="square"></i> Tout désélectionner
                                                </button>
                                            </div>

                                            <div class="card" style="max-height: 400px; overflow-y: auto;">
                                                <div class="list-group list-group-flush" id="contacts-list">
                                                    <?php
                                                    // Get contacts from database
                                                    $contacts = \Modules\Contacts\Models\Contact::orderBy('first_name', 'ASC')->get();

                                                    if (count($contacts) > 0):
                                                        foreach ($contacts as $contact):
                                                            if (!empty($contact->phone)):
                                                    ?>
                                                                <label class="list-group-item contact-item">
                                                                    <input class="form-check-input me-2 contact-checkbox" type="checkbox"
                                                                        name="contact_ids[]" value="<?= $contact->id ?>"
                                                                        data-phone="<?= htmlspecialchars($contact->phone) ?>"
                                                                        data-name="<?= htmlspecialchars($fullName) ?>">
                                                                    <strong><?= htmlspecialchars($fullName) ?></strong>
                                                                    <br>
                                                                    <small class="text-muted">
                                                                        <i data-feather="smartphone" class="feather-sm"></i>
                                                                        <?= htmlspecialchars($contact->phone) ?>
                                                                        <?php if (!empty($contact->email)): ?>
                                                                            | <i data-feather="mail" class="feather-sm"></i>
                                                                            <?= htmlspecialchars($contact->email) ?>
                                                                        <?php endif; ?>
                                                                    </small>
                                                                </label>
                                                        <?php
                                                            endif;
                                                        endforeach;
                                                    else:
                                                        ?>
                                                        <div class="list-group-item text-center text-muted py-4">
                                                            <i data-feather="users" style="width: 48px; height: 48px;"></i>
                                                            <p class="mb-0">Aucun contact disponible</p>
                                                            <a href="<?= url('/admin/contacts/create') ?>" class="btn btn-sm btn-primary mt-2">
                                                                <i data-feather="plus"></i> Ajouter un contact
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="alert alert-light-success">
                                            <i data-feather="users"></i>
                                            Contacts sélectionnés: <strong id="contacts-count" class="text-success">0</strong>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6 class="mb-3">Configuration</h6>

                                                <div class="form-group mb-3">
                                                    <label for="sender_name_id_contacts">Sender Name <span class="text-danger">*</span></label>
                                                    <select class="form-control" name="sender_name_id" required>
                                                        <option value="">-- Sélectionner --</option>
                                                        <?php if (isset($senderNames) && !empty($senderNames)): ?>
                                                            <?php foreach ($senderNames as $senderName): ?>
                                                                <option value="<?= $senderName->id ?>">
                                                                    <?= htmlspecialchars($senderName->name) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </select>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label for="campaign_name_contacts">Nom de la campagne</label>
                                                    <input type="text" class="form-control" name="campaign_name"
                                                        value="Contacts <?= date('d/m/Y H:i') ?>">
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label for="scheduled_at">Programmer l'envoi <span class="text-muted">(optionnel)</span></label>
                                                    <input type="datetime-local" class="form-control" name="scheduled_at">
                                                    <small class="form-text text-muted">Laissez vide pour envoi immédiat</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="message_contacts">Message <span class="text-danger">*</span></label>
                                    <textarea class="form-control message-field" id="message_contacts" name="message"
                                        rows="4" maxlength="160" required></textarea>
                                    <small class="form-text text-muted">
                                        <span class="char-counter">0</span>/160 caractères
                                    </small>
                                </div>

                                <div class="alert alert-light-warning">
                                    <i data-feather="alert-circle"></i>
                                    Coût estimé: <strong id="contacts-cost" class="text-warning">$0.00</strong>
                                    (<span id="contacts-count-display">0</span> SMS × $0.03)
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i data-feather="send"></i> Envoyer à <span id="contacts-count-btn">0</span> contacts
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<!-- SMS Import with Variables -->
<script src="<?= asset('assets/js/sms-import-variables.js') ?>"></script>

<script>
    // Character counter for all message fields
    document.querySelectorAll('.message-field').forEach(function(textarea) {
        const counter = textarea.parentElement.querySelector('.char-counter');
        textarea.addEventListener('input', function() {
            counter.textContent = this.value.length;
        });
    });

    // Single SMS character counter
    document.getElementById('message')?.addEventListener('input', function() {
        document.getElementById('char-count').textContent = this.value.length;
    });

    // Manual recipients counter
    document.getElementById('recipients_manual')?.addEventListener('input', function() {
        const text = this.value.trim();
        if (!text) {
            updateManualCount(0);
            return;
        }

        // Parse numbers (split by newline, comma, or semicolon)
        const numbers = text.split(/[\n,;]+/).map(s => s.trim()).filter(s => s.length > 0);
        updateManualCount(numbers.length);
    });

    function updateManualCount(count) {
        const cost = (count * 0.03).toFixed(2);
        document.getElementById('manual-count').textContent = count;
        document.getElementById('manual-count-display').textContent = count;
        document.getElementById('manual-count-btn').textContent = count;
        document.getElementById('manual-cost').textContent = '$' + cost;
    }

    function clearManualRecipients() {
        document.getElementById('recipients_manual').value = '';
        updateManualCount(0);
    }

    // Contacts selection counter
    document.querySelectorAll('.contact-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', updateContactsCount);
    });

    function updateContactsCount() {
        const count = document.querySelectorAll('.contact-checkbox:checked').length;
        const cost = (count * 0.03).toFixed(2);

        document.getElementById('contacts-count').textContent = count;
        document.getElementById('contacts-count-display').textContent = count;
        document.getElementById('contacts-count-btn').textContent = count;
        document.getElementById('contacts-cost').textContent = '$' + cost;
    }

    // Select all contacts
    document.getElementById('select-all-contacts')?.addEventListener('click', function() {
        document.querySelectorAll('.contact-checkbox').forEach(function(checkbox) {
            if (checkbox.closest('.contact-item').style.display !== 'none') {
                checkbox.checked = true;
            }
        });
        updateContactsCount();
    });

    // Unselect all contacts
    document.getElementById('unselect-all-contacts')?.addEventListener('click', function() {
        document.querySelectorAll('.contact-checkbox').forEach(function(checkbox) {
            checkbox.checked = false;
        });
        updateContactsCount();
    });

    // Contact search
    document.getElementById('contact-search')?.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();

        document.querySelectorAll('.contact-item').forEach(function(item) {
            const name = item.dataset.name ? item.dataset.name.toLowerCase() :
                item.querySelector('strong')?.textContent.toLowerCase() || '';
            const phone = item.dataset.phone ? item.dataset.phone :
                item.querySelector('small')?.textContent || '';

            if (name.includes(searchTerm) || phone.includes(searchTerm)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Initialize feather icons
    feather.replace();

    // Re-initialize feather icons when tab changes
    document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(function(tab) {
        tab.addEventListener('shown.bs.tab', function() {
            feather.replace();
        });
    });
</script>
@endsection