
@extends('backend.layouts.master')

@section('title', 'Configuration des Gateways Email')

@section('content')
<div class="container-fluid">
    <h2>Gateways Email</h2>
    <a href="<?= route('admin.email-marketing.gateways.create') ?>" class="btn btn-primary mb-3">Ajouter un Gateway</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Type</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($gateways as $gateway): ?>
            <tr>
                <td><?= htmlspecialchars($gateway['name']) ?></td>
                <td><?= htmlspecialchars($gateway['type']) ?></td>
                <td><?= $gateway['active'] ? 'Actif' : 'Inactif' ?></td>
                <td>
                    <a href="<?= route('admin.email-marketing.gateways.edit', ['name' => $gateway['name']]) ?>" class="btn btn-sm btn-warning">Modifier</a>
                    <a href="<?= route('admin.email-marketing.gateways.delete', ['name' => $gateway['name']]) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce gateway ?')">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
@endsection
