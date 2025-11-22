@extends('admin.layout')

@section('content')

<?php
// Breadcrumb
$breadcrumb = [
    ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
    ['label' => 'Utilisateurs (DataTables)']
];
component('breadcrumb');
?>

<!-- Messages Flash -->
<?php component('alerts'); ?>

<!-- Section: Actions rapides -->
<div class="row mb-3">
    <div class="col-12 text-end">
        <a href="<?= url('/admin/users/create') ?>" class="btn btn-primary">
            <i data-feather="plus"></i> Nouvel utilisateur
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php
        $card_title = "Liste des Utilisateurs (DataTables)";
        component('card-start');
        ?>

        <table id="usersTable" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom d'utilisateur</th>
                    <th>Email</th>
                    <th>Date de création</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- DataTables will populate this -->
            </tbody>
        </table>

        <?php
        component('card-end');
        ?>
    </div>
</div>

@endsection

@section('scripts')
<!-- DataTables CSS & JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#usersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= url('/api/users/datatable') ?>',
                type: 'GET'
            },
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'username',
                    name: 'username'
                },
                {
                    data: 'email',
                    name: 'email',
                    defaultContent: '<span class="text-muted">-</span>'
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    render: function(data) {
                        if (data) {
                            const date = new Date(data);
                            return date.toLocaleDateString('fr-FR') + ' ' + date.toLocaleTimeString('fr-FR');
                        }
                        return '<span class="text-muted">-</span>';
                    }
                },
                {
                    data: 'id',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    className: 'text-end',
                    render: function(data, type, row) {
                        return `
                        <a href="<?= url('/admin/users/') ?>${data}/edit" 
                           class="btn btn-sm btn-warning" 
                           title="Éditer">
                            <i data-feather="edit" style="width: 14px; height: 14px;"></i>
                        </a>
                        <button onclick="deleteUser(${data})" 
                                class="btn btn-sm btn-danger" 
                                title="Supprimer">
                            <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                        </button>
                    `;
                    }
                }
            ],
            order: [
                [0, 'desc']
            ],
            pageLength: 10,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
            },
            drawCallback: function() {
                // Réinitialiser les icônes Feather après chaque refresh
                feather.replace();
            }
        });
    });

    function deleteUser(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url('/admin/users/') ?>' + id + '/delete';

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_csrf_token';
            csrfInput.value = '<?= csrf_token() ?>';

            form.appendChild(csrfInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
@endsection