@extends('backend.layouts.master')

@section('title', 'Configuration AI')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Configuration AI</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Home</a></li>
                    <li class="breadcrumb-item"><a href="/admin/ai">AI</a></li>
                    <li class="breadcrumb-item active">Settings</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5>Paramètres Globaux</h5>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Clé API OpenAI</label>
                            <input class="form-control" type="password" name="openai_api_key" value="<?= getenv('OPENAI_API_KEY') ? '****************' : '' ?>" placeholder="sk-...">
                            <small class="text-muted">Définie dans le fichier .env</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Modèle par défaut</label>
                            <select class="form-select" name="default_model">
                                <option value="gpt-3.5-turbo" selected>gpt-3.5-turbo</option>
                                <option value="gpt-4">gpt-4</option>
                            </select>
                        </div>
                        <div class="card-footer text-end">
                            <button class="btn btn-primary" type="submit">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection