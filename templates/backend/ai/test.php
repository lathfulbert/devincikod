@extends('backend.layouts.master')

@section('title', 'Test AI')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Test AI</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Home</a></li>
                    <li class="breadcrumb-item"><a href="/admin/ai">AI</a></li>
                    <li class="breadcrumb-item active">Test</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12 col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5>Tester une requête</h5>
                    <p class="text-muted mb-0"><i data-feather="cpu"></i> Modèle actuel: <strong><?= $model ?? 'gpt-3.5-turbo' ?></strong></p>
                </div>
                <div class="card-body">
                    <form action="<?= url('/admin/ai/test') ?>" method="POST">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Prompt</label>
                            <textarea class="form-control" name="prompt" rows="5" placeholder="Entrez votre prompt ici..." required></textarea>
                        </div>
                        <div class="card-footer text-end">
                            <button class="btn btn-primary" type="submit"><i data-feather="send"></i> Envoyer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5>Réponse</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($response) && !empty($response)): ?>
                        <div class="alert alert-light-success" role="alert">
                            <pre style="white-space: pre-wrap;"><?= htmlspecialchars($response) ?></pre>
                        </div>
                    <?php else: ?>
                        <p class="text-muted"><i data-feather="message-circle"></i> La réponse s'affichera ici après l'envoi.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection