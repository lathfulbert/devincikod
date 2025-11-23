@extends('backend.layouts.master')

@section('title', 'Nouvel Article')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Nouvel Article</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/admin/dashboard') ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/akpa') ?>">Akpa</a></li>
                    <li class="breadcrumb-item active">Créer</li>
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
                    <h5>Créer un Article</h5>
                </div>
                <div class="card-body">
                    <form action="<?= url('/admin/akpa/store') ?>" method="POST">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label class="form-label">Titre</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contenu</label>
                            <textarea class="form-control" name="content" rows="10" required></textarea>
                        </div>

                        <div class="card-footer text-end">
                            <a href="<?= url('/admin/akpa') ?>" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">Publier</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection