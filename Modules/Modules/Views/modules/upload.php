@extends('backend.layouts.master')

@section('title', 'Installer un Module')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Installer un Module</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/admin/dashboard') ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/modules') ?>">Modules</a></li>
                    <li class="breadcrumb-item active">Installer</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <h5><i data-feather="package"></i> Uploader un module</h5>
                    <p class="text-muted mb-0">Téléchargez un fichier ZIP contenant un module valide</p>
                </div>
                <div class="card-body">
                    <div class="alert alert-light-info" role="alert">
                        <h6 class="alert-heading"><i data-feather="info"></i> Informations</h6>
                        <ul class="mb-0">
                            <li>Le fichier doit être au format <strong>ZIP</strong></li>
                            <li>Taille maximale: <strong>50 MB</strong></li>
                            <li>Le module doit contenir un fichier <code>module.json</code> valide</li>
                            <li>Structure attendue: <code>MonModule/module.json</code> ou <code>module.json</code> à la racine</li>
                        </ul>
                    </div>

                    <form action="<?= url('/admin/modules/process-upload') ?>" method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>

                        <div class="mb-4">
                            <label class="form-label">Fichier ZIP du module <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" name="module_zip" accept=".zip" required>
                            <small class="text-muted">Sélectionnez le fichier ZIP contenant votre module</small>
                        </div>

                        <div class="mb-3">
                            <h6>Structure requise du module.json :</h6>
                            <pre class="bg-light p-3 rounded"><code>{
  "name": "MonModule",
  "version": "1.0.0",
  "description": "Description du module",
  "author": "Votre Nom",
  "license": "MIT",
  "dependencies": [],
  "autoload": {
    "routes": true,
    "migrations": true,
    "services": false,
    "permissions": false,
    "views": true,
    "assets": false,
    "config": false
  }
}</code></pre>
                        </div>

                        <div class="card-footer text-end">
                            <a href="<?= url('/admin/modules') ?>" class="btn btn-secondary">
                                <i data-feather="x"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="upload"></i> Installer le module
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5><i data-feather="help-circle"></i> Guide de création de module</h5>
                </div>
                <div class="card-body">
                    <h6>1. Structure de base</h6>
                    <pre class="bg-light p-2 rounded"><code>MonModule/
├── module.json (obligatoire)
├── MonModuleModule.php (classe principale)
├── Controllers/
│   └── MonController.php
├── Views/
│   └── index.php
├── Database/
│   └── Migrations/
│       └── 001_CreateTable.php
└── README.md</code></pre>

                    <h6 class="mt-3">2. Classe principale (exemple)</h6>
                    <pre class="bg-light p-2 rounded"><code>&lt;?php
namespace Modules\MonModule;

use App\Core\Module\AbstractModule;

class MonModuleModule extends AbstractModule
{
    public function getRoutes(): array
    {
        return [
            ['GET', '/mon-module', ['Modules\MonModule\Controllers\MonController', 'index']]
        ];
    }

    protected function getModulePath(): string
    {
        return __DIR__;
    }
}</code></pre>

                    <h6 class="mt-3">3. Compression</h6>
                    <p>Compressez votre dossier <code>MonModule</code> en ZIP, puis uploadez-le via ce formulaire.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection