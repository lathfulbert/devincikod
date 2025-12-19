@extends('backend.layouts.master')

@section('title', $title ?? 'Documentation API SMS')

@section('styles')
<style>
    .api-docs {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    .api-docs h1 {
        color: #2c3e50;
        border-bottom: 2px solid #3498db;
        padding-bottom: 10px;
        margin-top: 30px;
    }
    .api-docs h2 {
        color: #34495e;
        margin-top: 25px;
        margin-bottom: 15px;
    }
    .api-docs h3 {
        color: #7f8c8d;
        margin-top: 20px;
    }
    .api-docs pre {
        background-color: #2d2d2d;
        color: #f8f8f2;
        padding: 15px;
        border-radius: 5px;
        overflow-x: auto;
        font-size: 14px;
    }
    .api-docs code {
        background-color: #f4f4f4;
        padding: 2px 6px;
        border-radius: 3px;
        font-family: 'Courier New', Courier, monospace;
        color: #e74c3c;
    }
    .api-docs pre code {
        background-color: transparent;
        color: #f8f8f2;
        padding: 0;
    }
    .api-docs table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }
    .api-docs table th {
        background-color: #3498db;
        color: white;
        padding: 12px;
        text-align: left;
    }
    .api-docs table td {
        border: 1px solid #ddd;
        padding: 10px;
    }
    .api-docs table tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .endpoint-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 3px;
        font-weight: bold;
        font-size: 12px;
        margin-right: 5px;
    }
    .endpoint-badge.post {
        background-color: #27ae60;
        color: white;
    }
    .endpoint-badge.get {
        background-color: #3498db;
        color: white;
    }
    .alert-api {
        padding: 15px;
        margin: 20px 0;
        border-left: 4px solid #3498db;
        background-color: #e8f4f8;
    }
</style>
@endsection

@section('content')

<div class="container-fluid">
    <?php
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'SMS', 'url' => '/admin/sms'],
        ['label' => 'Documentation API']
    ];
    component('breadcrumb', ['breadcrumb' => $breadcrumb]);
    ?>
</div>

<div class="container-fluid">
    <?php component('alerts'); ?>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <?php
            component('card-start', [
                'card_title' => "Documentation API SMS",
                'card_actions' => '<a href="' . url('/admin/apikeys') . '" class="btn btn-primary btn-sm"><i data-feather="key"></i> Gérer mes clés API</a>'
            ]);
            ?>

            <div class="alert alert-info alert-api">
                <i data-feather="info"></i>
                <strong>Important:</strong> Vous devez générer une clé API depuis la page
                <a href="<?= url('/admin/apikeys') ?>">Gérer mes clés API</a> pour utiliser l'API SMS.
            </div>

            <div class="api-docs">
                <?php if ($has_parsedown): ?>
                    <?= $html ?>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i data-feather="alert-triangle"></i>
                        La bibliothèque Parsedown n'est pas installée. Affichage du contenu brut.
                    </div>
                    <pre><?= htmlspecialchars($markdown) ?></pre>
                <?php endif; ?>
            </div>

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
