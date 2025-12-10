@extends('backend.layouts.master')

@section('title', $title ?? 'Contacts')

@section('content')

<div class="container-fluid">
    <?php
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'Contacts']
    ];
    component('breadcrumb');
    ?>
</div>

<div class="container-fluid">
    <?php component('alerts'); ?>
</div>

<div class="container-fluid">
    <!-- Filters & Actions -->
    <div class="row mb-3">
        <div class="col-md-8">
            <form method="GET" action="<?= url('/admin/contacts') ?>" class="row g-2">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher par nom, téléphone ou email..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <select name="is_active" class="form-select" onchange="this.form.submit()">
                        <option value="">Tous les statuts</option>
                        <option value="1" <?= ($filters['is_active'] ?? '') === '1' ? 'selected' : '' ?>>Actifs</option>
                        <option value="0" <?= ($filters['is_active'] ?? '') === '0' ? 'selected' : '' ?>>Inactifs</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i data-feather="search"></i> Rechercher
                    </button>
                </div>
            </form>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?= url('/admin/contacts/create') ?>" class="btn btn-primary">
                <i data-feather="plus"></i> Nouveau Contact
            </a>
            <a href="<?= url('/admin/contacts/fields') ?>" class="btn btn-outline-secondary">
                <i data-feather="settings"></i> Champs
            </a>
        </div>
    </div>

    <!-- Contacts Table -->
    <div class="row">
        <div class="col-12">
            <?php
            component('card-start', ['card_title' => "Liste des Contacts"]);
            ?>

            <div class="table-responsive">
                <table id="contactsTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Téléphone</th>
                            <th>Email</th>
                            <?php foreach ($fieldDefinitions ?? [] as $field): ?>
                                <th><?= htmlspecialchars($field->name) ?></th>
                            <?php endforeach; ?>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($contacts)): ?>
                            <?php foreach ($contacts as $contact): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($contact->getFullName()) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($contact->phone) ?></td>
                                    <td><?= htmlspecialchars($contact->email ?? '-') ?></td>
                                    <?php foreach ($fieldDefinitions ?? [] as $field): ?>
                                        <td><?= htmlspecialchars($contact->getCustomField($field->slug) ?? '-') ?></td>
                                    <?php endforeach; ?>
                                    <td>
                                        <?php if ($contact->is_active): ?>
                                            <span class="badge bg-success">Actif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= url('/admin/contacts/' . $contact->id . '/edit') ?>"
                                            class="btn btn-sm btn-warning" title="Modifier">
                                            <i data-feather="edit" style="width: 14px; height: 14px;"></i>
                                        </a>
                                        <a href="<?= url('/admin/contacts/' . $contact->id . '/delete') ?>"
                                            class="btn btn-sm btn-danger" title="Supprimer"
                                            onclick="return confirm('Supprimer ce contact ?')">
                                            <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= 5 + count($fieldDefinitions ?? []) ?>" class="text-center text-muted">
                                    <i data-feather="inbox"></i> Aucun contact trouvé
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php
            $card_footer = "Total : " . count($contacts ?? []) . " contact(s)";
            component('card-end');
            ?>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<?php
$datatable_id = 'contactsTable';
component('datatable-init');
?>
<script>
    feather.replace();
</script>
@endsection