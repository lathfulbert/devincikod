@extends('backend.layouts.master')

@section('title', 'Liste des Articles')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Akpa - Articles</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/admin/dashboard') ?>">Home</a></li>
                    <li class="breadcrumb-item active">Akpa</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Liste des Articles</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <a href="<?= url('/admin/akpa/create') ?>" class="btn btn-primary">
                            <i data-feather="plus"></i> Nouvel Article
                        </a>
                    </div>

                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Titre</th>
                                <th>Auteur</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td><?= $post['id'] ?></td>
                                    <td><?= htmlspecialchars($post['title']) ?></td>
                                    <td><?= htmlspecialchars($post['author']) ?></td>
                                    <td><?= $post['created_at'] ?></td>
                                    <td>
                                        <a href="<?= url('/admin/akpa/' . $post['id'] . '/edit') ?>" class="btn btn-sm btn-info">
                                            <i data-feather="edit"></i>
                                        </a>
                                        <form action="<?= url('/admin/akpa/' . $post['id'] . '/delete') ?>" method="POST" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet article ?')">
                                                <i data-feather="trash-2"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection