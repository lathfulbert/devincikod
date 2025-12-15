@extends('backend.layouts.master')

@section('title', 'Ma Clé API')

@section('content')

<div class="container-fluid">
    <?php
    // Breadcrumb
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'API Keys']
    ];
    component('breadcrumb', $breadcrumb);
    ?>
</div>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">🔑 Ma Clé API Personnelle</h5>
                </div>
                <div class="card-body">
                    <?php component('alerts'); ?>

                    <?php if (isset($_SESSION['new_api_key'])): ?>
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">
                                <i class="fa fa-exclamation-triangle"></i> Clé API générée - Copiez-la maintenant !
                            </h6>
                            <p class="mb-2">Cette clé ne sera plus affichée. Conservez-la en lieu sûr.</p>
                            <div class="input-group">
                                <input type="text" class="form-control font-monospace" id="newApiKey"
                                    value="<?= $_SESSION['new_api_key'] ?>" readonly>
                                <button class="btn btn-outline-primary" type="button" onclick="copyKey()">
                                    <i class="fa fa-copy"></i> Copier
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                    <!-- ...suite du contenu... -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
