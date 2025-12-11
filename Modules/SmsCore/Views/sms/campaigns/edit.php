@extends('backend.layouts.master')

@section('title', $title ?? 'Modifier Campagne SMS')

@section('content')

<div class="container-fluid">
    <?php
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'Campagnes SMS', 'url' => '/admin/sms/campaigns'],
        ['label' => 'Modifier']
    ];
    component('breadcrumb', ['breadcrumb' => $breadcrumb]);
    ?>
</div>

<div class="container-fluid">
    <?php component('alerts'); ?>
</div>

<div class="container-fluid">
    <form action="<?= url('/admin/sms/campaigns/' . $campaign->id . '/update') ?>" method="POST" id="campaignForm">
        <?= csrf_field() ?>

        <div class="row">
            <!-- Formulaire principal -->
            <div class="col-lg-8">
                <?php
                component('card-start', ['card_title' => "Informations de la campagne"]);
                ?>

                <div class="mb-3">
                    <label for="name" class="form-label">Nom de la campagne <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control"
                        value="<?= htmlspecialchars($campaign->name ?? '') ?>" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                    <textarea name="message" id="message" class="form-control" rows="6" required><?= htmlspecialchars($campaign->message ?? '') ?></textarea>
                    <small class="form-text text-muted">
                        <strong>Caractères:</strong> <span id="charCount">0</span> / 160
                        | <strong>SMS:</strong> <span id="smsCount">0</span>
                    </small>
                </div>

                <div class="mb-3">
                    <label for="sender_name_id" class="form-label">Sender Name <span class="text-danger">*</span></label>
                    <select class="form-control" id="sender_name_id" name="sender_name_id" required>
                        <option value="">-- Select Sender Name --</option>
                        <?php if (isset($senderNames) && !empty($senderNames)): ?>
                            <?php foreach ($senderNames as $senderName): ?>
                                <option value="<?= $senderName->id ?>"
                                    <?= ($campaign->sender_name_id == $senderName->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($senderName->name) ?>
                                    <?php if ($senderName->operator): ?>
                                        (<?= htmlspecialchars($senderName->operator) ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>No sender names assigned to you</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="use_personalization" id="use_personalization"
                            class="form-check-input" value="1"
                            <?= ($campaign->use_personalization ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="use_personalization">
                            Activer la personnalisation (placeholders)
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="scheduled_at" class="form-label">
                        Programmer l'envoi (optionnel)
                    </label>
                    <?php
                    // Format scheduled_at for datetime-local input (YYYY-MM-DDTHH:MM)
                    $scheduledValue = '';
                    if (!empty($campaign->scheduled_at)) {
                        $scheduledValue = date('Y-m-d\TH:i', strtotime($campaign->scheduled_at));
                    }
                    ?>
                    <input type="datetime-local" name="scheduled_at" id="scheduled_at"
                        class="form-control"
                        value="<?= htmlspecialchars($scheduledValue) ?>">
                    <small class="form-text text-muted">
                        Laissez vide pour envoyer immédiatement. La date et l'heure doivent être dans le futur.
                    </small>
                    <div id="scheduledDateError" class="text-danger small" style="display: none;">
                        La date et l'heure doivent être dans le futur.
                    </div>
                </div>

                <?php component('card-end'); ?>

                <?php
                component('card-start', ['card_title' => "Sélection des destinataires"]);
                ?>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0">
                            Contacts <span class="text-danger">*</span>
                        </label>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                                Tout sélectionner
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">
                                Tout désélectionner
                            </button>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <strong><span id="selectedCount">0</span></strong> contact(s) sélectionné(s)
                    </div>

                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-hover">
                            <thead class="table-light" style="position: sticky; top: 0;">
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="selectAllCheckbox" onchange="toggleAll(this)">
                                    </th>
                                    <th>Nom</th>
                                    <th>Téléphone</th>
                                    <th>Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($contacts)): ?>
                                    <?php foreach ($contacts as $contact): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="contact_ids[]"
                                                    value="<?= $contact->id ?>"
                                                    class="contact-checkbox"
                                                    data-phone="<?= htmlspecialchars($contact->phone) ?>"
                                                    <?= in_array($contact->id, $selectedContactIds ?? []) ? 'checked' : '' ?>
                                                    onchange="updateCount()">
                                            </td>
                                            <td><?= htmlspecialchars($contact->getFullName()) ?></td>
                                            <td><?= htmlspecialchars($contact->phone) ?></td>
                                            <td><?= htmlspecialchars($contact->email ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            Aucun contact actif.
                                            <a href="<?= url('/admin/contacts/create') ?>">Créer un contact</a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php component('card-end'); ?>
            </div>

            <!-- Sidebar - Placeholders & Preview -->
            <div class="col-lg-4">
                <?php
                component('card-start', ['card_title' => "Placeholders disponibles"]);
                ?>

                <p class="text-muted small">Cliquez pour insérer dans le message</p>

                <h6 class="mt-3">Champs de base</h6>
                <div class="d-flex flex-wrap gap-1">
                    <?php foreach ($placeholders['basic'] ?? [] as $placeholder => $description): ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                            onclick="insertPlaceholder('<?= $placeholder ?>')"
                            title="<?= htmlspecialchars($description) ?>">
                            <?= htmlspecialchars($placeholder) ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <?php if (!empty($placeholders['custom'])): ?>
                    <h6 class="mt-3">Champs personnalisés</h6>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach ($placeholders['custom'] ?? [] as $placeholder => $description): ?>
                            <button type="button" class="btn btn-sm btn-outline-info"
                                onclick="insertPlaceholder('<?= $placeholder ?>')"
                                title="<?= htmlspecialchars($description) ?>">
                                <?= htmlspecialchars($placeholder) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php component('card-end'); ?>

                <?php
                component('card-start', ['card_title' => "Preview"]);
                ?>

                <div id="previewSection" style="display: none;">
                    <p class="text-muted small">
                        Preview pour: <strong id="previewContactName"></strong>
                    </p>
                    <div class="alert alert-light border" id="previewMessage">
                        Sélectionnez un contact et écrivez un message pour voir le preview...
                    </div>
                </div>

                <button type="button" class="btn btn-sm btn-outline-primary w-100"
                    onclick="showPreview()" id="previewBtn" disabled>
                    <i data-feather="eye"></i> Voir Preview
                </button>

                <?php component('card-end'); ?>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i data-feather="save"></i> Mettre à Jour la Campagne
                    </button>
                    <a href="<?= url('/admin/sms/campaigns/' . $campaign->id) ?>" class="btn btn-secondary w-100 mt-2">
                        <i data-feather="x"></i> Annuler
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
    feather.replace();

    // Server timezone from PHP
    const SERVER_TIMEZONE = '<?= get_timezone() ?>';
    const TIMEZONE_OFFSET = <?= (new DateTime('now', new DateTimeZone(get_timezone())))->getOffset() / 3600 ?>;

    // Character counter
    const messageTextarea = document.getElementById('message');
    const charCount = document.getElementById('charCount');
    const smsCount = document.getElementById('smsCount');

    // Initial count
    function updateCharCount() {
        const length = messageTextarea.value.length;
        charCount.textContent = length;
        smsCount.textContent = Math.ceil(length / 160) || 0;
        updatePreviewButton();
    }

    messageTextarea.addEventListener('input', updateCharCount);

    // Contact selection
    function updateCount() {
        const checked = document.querySelectorAll('.contact-checkbox:checked').length;
        document.getElementById('selectedCount').textContent = checked;
        document.getElementById('selectAllCheckbox').checked =
            checked === document.querySelectorAll('.contact-checkbox').length;

        updatePreviewButton();
    }

    function toggleAll(checkbox) {
        document.querySelectorAll('.contact-checkbox').forEach(cb => {
            cb.checked = checkbox.checked;
        });
        updateCount();
    }

    function selectAll() {
        document.querySelectorAll('.contact-checkbox').forEach(cb => cb.checked = true);
        updateCount();
    }

    function deselectAll() {
        document.querySelectorAll('.contact-checkbox').forEach(cb => cb.checked = false);
        updateCount();
    }

    // Insert placeholder
    function insertPlaceholder(placeholder) {
        const textarea = document.getElementById('message');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;

        textarea.value = text.substring(0, start) + placeholder + text.substring(end);
        textarea.focus();
        textarea.selectionStart = textarea.selectionEnd = start + placeholder.length;

        // Trigger input event
        updateCharCount();
    }

    // Preview
    function updatePreviewButton() {
        const hasMessage = messageTextarea.value.trim().length > 0;
        const hasContacts = document.querySelectorAll('.contact-checkbox:checked').length > 0;
        document.getElementById('previewBtn').disabled = !hasMessage || !hasContacts;
    }

    function showPreview() {
        const message = messageTextarea.value;
        const checkedContact = document.querySelector('.contact-checkbox:checked');

        if (!checkedContact) {
            alert('Sélectionnez au moins un contact');
            return;
        }

        const contactId = checkedContact.value;

        // AJAX request for preview
        fetch('<?= url('/admin/sms/campaigns/preview') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `message=${encodeURIComponent(message)}&contact_id=${contactId}&<?= csrf_token() ?>`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('previewContactName').textContent = data.contact_name;
                    document.getElementById('previewMessage').textContent = data.preview;
                    document.getElementById('previewSection').style.display = 'block';
                } else {
                    alert('Erreur: ' + (data.error || 'Impossible de générer le preview'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erreur de connexion');
            });
    }

    // Form validation
    document.getElementById('campaignForm').addEventListener('submit', function(e) {
        const checked = document.querySelectorAll('.contact-checkbox:checked').length;
        if (checked === 0) {
            e.preventDefault();
            alert('Veuillez sélectionner au moins un contact');
        }
    });

    // Scheduled date validation
    const scheduledInput = document.getElementById('scheduled_at');
    const scheduledError = document.getElementById('scheduledDateError');

    // Set minimum datetime to current time
    function setMinDateTime() {
        const now = new Date();
        // Add 5 minutes buffer to account for form filling time
        now.setMinutes(now.getMinutes() + 5);
        const minDateTime = now.toISOString().slice(0, 16);
        scheduledInput.min = minDateTime;
    }

    // Validate scheduled date on change
    scheduledInput.addEventListener('change', function() {
        if (this.value) {
            const selectedDate = new Date(this.value);
            const now = new Date();

            if (selectedDate <= now) {
                scheduledError.style.display = 'block';
                this.setCustomValidity('La date doit être dans le futur');
                this.classList.add('is-invalid');
            } else {
                scheduledError.style.display = 'none';
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        } else {
            scheduledError.style.display = 'none';
            this.setCustomValidity('');
            this.classList.remove('is-invalid');
        }
    });

    // Form validation on submit
    document.getElementById('campaignForm').addEventListener('submit', function(e) {
        // Validate scheduled date if provided
        if (scheduledInput.value) {
            const selectedDate = new Date(scheduledInput.value);
            const now = new Date();

            if (selectedDate <= now) {
                e.preventDefault();
                scheduledError.style.display = 'block';
                scheduledInput.classList.add('is-invalid');
                scheduledInput.focus();
                alert('La date et l\'heure programmées doivent être dans le futur');
                return false;
            }
        }
    });

    // Initialize
    setMinDateTime();
    updateCount();
    updateCharCount();
</script>
@endsection
