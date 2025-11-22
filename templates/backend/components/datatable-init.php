<?php

/**
 * Component: DataTable Wrapper
 * Configuration et initialisation d'une DataTable
 * 
 * Usage:
 *   $datatable_id = 'myTable'; // ID de la table
 *   $datatable_config = [ // Configuration optionnelle
 *       'order' => [[0, 'desc']],
 *       'pageLength' => 25
 *   ];
 */

$table_id = $datatable_id ?? 'dataTable';
$config = $datatable_config ?? [];

// Configuration par défaut
$default_config = [
    'order' => [[0, 'asc']],
    'pageLength' => 10,
    'language' => [
        'url' => '//cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
    ]
];

$final_config = array_merge($default_config, $config);
?>

<script>
    $(document).ready(function() {
        $('#<?= $table_id ?>').DataTable(<?= json_encode($final_config) ?>);
    });
</script>