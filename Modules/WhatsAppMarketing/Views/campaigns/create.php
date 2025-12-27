@extends('backend.layouts.master')

@section('title', $title)

@section('content')

<div class="container-fluid">
    <?php
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'WhatsApp', 'url' => '/admin/whatsapp'],
        ['label' => 'Campagnes', 'url' => '/admin/whatsapp/campaigns'],
        ['label' => 'Créer']
    ];
    component('breadcrumb');
    ?>

    <?php component('alerts'); ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Nouvelle Campagne</h6>
        </div>
        <div class="card-body">
            <form action="<?= url('/admin/whatsapp/campaigns/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Nom de la campagne</label>
                    <input type="text" name="name" class="form-control" required placeholder="Ex: Promo Noël 2024">
                </div>

                <div class="mb-3">
                    <label class="form-label">Template (Modèle approuvé)</label>
                    <select name="template_id" class="form-control" required>
                        <option value="">-- Sélectionner un template --</option>
                        <?php foreach ($templates as $template): ?>
                            <option value="<?= $template->id ?>">
                                <?= htmlspecialchars($template->name) ?> (<?= $template->language ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Seuls les templates approuvés apparaissent ici.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Audience (Destinataires)</label>
                    <select name="audience_type" class="form-control">
                        <option value="all">Tous les contacts (Opt-in WhatsApp)</option>
                        <option value="segment">Segment personnalisé (Coming soon)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Planification</label>
                    <input type="datetime-local" name="scheduled_at" class="form-control">
                    <small class="text-muted">Laissez vide pour envoyer immédiatement.</small>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i data-feather="send"></i> Créer et Lancer
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    feather.replace();
</script>
@endsection