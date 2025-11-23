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
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Prompt</label>
                            <textarea class="form-control" name="prompt" rows="5" placeholder="Entrez votre prompt ici..."></textarea>
                        </div>
                        <div class="card-footer text-end">
                            <button class="btn btn-primary" type="submit">Envoyer</button>
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
                    <?php if (isset($response)): ?>
                        <div class="alert alert-light-primary" role="alert">
                            <pre><?= htmlspecialchars($response) ?></pre>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">La réponse s'affichera ici.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection