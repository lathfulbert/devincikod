@extends('backend.layouts.master')

@section('title', 'Gestion AI')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Gestion AI</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Home</a></li>
                    <li class="breadcrumb-item active">AI</li>
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
                    <h5>Agents AI Enregistrés</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Module</th>
                                    <th>Classe</th>
                                    <th>Modèle</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($agents)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">Aucun agent enregistré.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($agents as $module => $agent): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($module) ?></td>
                                            <td><?= get_class($agent) ?></td>
                                            <td><?= method_exists($agent, 'getModel') ? htmlspecialchars($agent->getModel()) : 'N/A' ?></td>
                                            <td>
                                                <a href="/admin/ai/settings" class="btn btn-sm btn-primary">Configurer</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-sm-12">
            <a href="/admin/ai/test" class="btn btn-info">Tester l'IA</a>
            <a href="/admin/ai/settings" class="btn btn-secondary">Paramètres</a>
            <a href="/admin/ai/logs" class="btn btn-warning">Logs</a>
        </div>
    </div>
</div>
@endsection