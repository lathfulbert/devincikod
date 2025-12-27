@extends('backend.layouts.master')

@section('title', $title ?? 'Modifier Contact')

@section('content')

<div class="container-fluid">
    <?php
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'Contacts', 'url' => '/admin/contacts'],
        ['label' => 'Modifier']
    ];
    component('breadcrumb');
    ?>
</div>

<div class="container-fluid">
    <?php component('alerts'); ?>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <?php
            component('card-start', ['card_title' => "Modifier le contact : " . htmlspecialchars($contact->getFullName())]);
            ?>

            <form action="<?= url('admin/contacts/' . $contact->id . '/update') ?>" method="POST">
                <?= csrf_field() ?>

                <h5 class="mb-3">Informations de base</h5>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label">Prénom <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" id="first_name" class="form-control"
                            value="<?= htmlspecialchars($contact->first_name) ?>" required autofocus>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="last_name" class="form-label">Nom</label>
                        <input type="text" name="last_name" id="last_name" class="form-control"
                            value="<?= htmlspecialchars($contact->last_name ?? '') ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                        <input type="tel" name="phone" id="phone" class="form-control"
                            value="<?= htmlspecialchars($contact->phone) ?>" required>
                        <small class="form-text text-muted">Format: +221 77 123 45 67</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control"
                            value="<?= htmlspecialchars($contact->email ?? '') ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input"
                            value="1" <?= $contact->is_active ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Contact actif</label>
                    </div>
                </div>

                <?php if (!empty($fieldDefinitions)): ?>
                    <hr class="my-4">
                    <h5 class="mb-3">Champs personnalisés</h5>

                    <?php foreach ($fieldDefinitions as $field): ?>
                        <?php $currentValue = $contact->getCustomField($field->slug, $field->default_value); ?>

                        <div class="mb-3">
                            <label for="custom_<?= $field->slug ?>" class="form-label">
                                <?= htmlspecialchars($field->name) ?>
                                <?php if ($field->is_required): ?>
                                    <span class="text-danger">*</span>
                                <?php endif; ?>
                            </label>

                            <?php if ($field->type === 'textarea'): ?>
                                <textarea
                                    name="custom_fields[<?= $field->slug ?>]"
                                    id="custom_<?= $field->slug ?>"
                                    class="form-control"
                                    rows="3"
                                    placeholder="<?= htmlspecialchars($field->placeholder ?? '') ?>"
                                    <?= $field->is_required ? 'required' : '' ?>><?= htmlspecialchars($currentValue ?? '') ?></textarea>

                            <?php elseif ($field->type === 'select'): ?>
                                <select
                                    name="custom_fields[<?= $field->slug ?>]"
                                    id="custom_<?= $field->slug ?>"
                                    class="form-select"
                                    <?= $field->is_required ? 'required' : '' ?>>
                                    <option value="">-- Sélectionner --</option>
                                    <?php foreach ($field->getOptions() as $option): ?>
                                        <option value="<?= htmlspecialchars($option) ?>"
                                            <?= $currentValue == $option ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($option) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                            <?php else: ?>
                                <input
                                    type="<?= $field->type === 'number' ? 'number' : ($field->type === 'date' ? 'date' : 'text') ?>"
                                    name="custom_fields[<?= $field->slug ?>]"
                                    id="custom_<?= $field->slug ?>"
                                    class="form-control"
                                    value="<?= htmlspecialchars($currentValue ?? '') ?>"
                                    placeholder="<?= htmlspecialchars($field->placeholder ?? '') ?>"
                                    <?= $field->is_required ? 'required' : '' ?>>
                            <?php endif; ?>

                            <?php if ($field->help_text): ?>
                                <small class="form-text text-muted"><?= htmlspecialchars($field->help_text) ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save"></i> Mettre à jour
                    </button>
                    <a href="<?= url('admin/contacts') ?>" class="btn btn-secondary">
                        <i data-feather="x"></i> Annuler
                    </a>
                    <a href="<?= url('admin/contacts/' . $contact->id . '/delete') ?>"
                        class="btn btn-danger float-end"
                        onclick="return confirm('Supprimer définitivement ce contact ?')">
                        <i data-feather="trash-2"></i> Supprimer
                    </a>
                </div>
            </form>

            <?php component('card-end'); ?>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    feather.replace();
</script>
@endsection