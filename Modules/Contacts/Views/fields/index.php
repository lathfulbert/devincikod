@extends('backend.layouts.master')

@section('title', 'Champs Personnalisés')

@section('content')

<div class="container-fluid">
    <?php
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'Contacts', 'url' => '/admin/contacts'],
        ['label' => 'Champs Personnalisés']
    ];
    component('breadcrumb');
    ?>
</div>

<div class="container-fluid">
    <?php component('alerts'); ?>
</div>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12 text-end">
            <a href="<?= url('/admin/contacts/fields/create') ?>" class="btn btn-primary">
                <i data-feather="plus"></i> Nouveau Champ
            </a>
            <a href="<?= url('/admin/contacts') ?>" class="btn btn-outline-secondary">
                <i data-feather="arrow-left"></i> Retour aux Contacts
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <?php
            $card_title = "Champs Personnalisés";
            $card_subtitle = "Définissez des champs illimités pour personnaliser vos contacts";
            component('card-start');
            ?>

            <div class="alert alert-info">
                <i data-feather="info"></i>
                <strong>Placeholders pour SMS:</strong> Dans vos messages SMS, utilisez le format <code>&#123;&#123;custom.slug&#125;&#125;</code>.
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 30px;">#</th>
                            <th>Nom</th>
                            <th>Slug</th>
                            <th>Type</th>
                            <th>Requis</th>
                            <th>Ordre</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($fields)): ?>
                            <?php foreach ($fields as $field): ?>
                                <tr>
                                    <td><?= $field->id ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($field->name) ?></strong>
                                        <?php if ($field->help_text): ?>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars($field->help_text) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <code>custom.<?= htmlspecialchars($field->slug) ?></code>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= ucfirst($field->type) ?></span>
                                        <?php if ($field->type === 'select' && $field->options): ?>
                                            <br>
                                            <small class="text-muted">
                                                <?= count($field->getOptions()) ?> option(s)
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($field->is_required): ?>
                                            <span class="badge bg-warning">Requis</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark">Optionnel</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $field->sort_order ?? 0 ?></td>
                                    <td class="text-end">
                                        <a href="<?= url('/admin/contacts/fields/' . $field->id . '/edit') ?>"
                                            class="btn btn-sm btn-warning" title="Modifier">
                                            <i data-feather="edit" style="width: 14px; height: 14px;"></i>
                                        </a>
                                        <a href="<?= url('/admin/contacts/fields/' . $field->id . '/delete') ?>"
                                            class="btn btn-sm btn-danger" title="Supprimer"
                                            onclick="return confirm('Supprimer ce champ ? Les données existantes seront conservées.')">
                                            <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i data-feather="inbox"></i><br>
                                    Aucun champ personnalisé créé.<br>
                                    <a href="<?= url('/admin/contacts/fields/create') ?>">Créez votre premier champ</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php
            $card_footer = "Total : " . count($fields ?? []) . " champ(s) personnalisé(s)";
            component('card-end');
            ?>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    feather.replace();
</script>
@endsection