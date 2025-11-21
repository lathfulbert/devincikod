@extends('admin.layout')

@section('content')
<div class="header">
    <h1><?= $title ?? 'Statistiques du Cache' ?></h1>
    <div>
        <a href="<?= url('/admin/cache') ?>" class="btn btn-primary">Configuration</a>
    </div>
</div>

<?php
// Afficher les messages flash
if (isset($_SESSION['flash'])) {
    foreach ($_SESSION['flash'] as $type => $message) {
        echo "<div style='padding: 10px; margin-bottom: 15px; border-radius: 4px; background: " . ($type === 'success' ? '#d4edda' : '#f8d7da') . "; color: " . ($type === 'success' ? '#155724' : '#721c24') . ";'>{$message}</div>";
    }
    unset($_SESSION['flash']);
}
?>

<div class="card">
    <div class="header">
        <h2>Statistiques du Cache</h2>
        <form method="POST" action="<?= url('/admin/cache/clear') ?>" onsubmit="return confirm('Êtes-vous sûr de vouloir vider tout le cache ?');">
            <button type="submit" class="btn btn-danger">Vider le cache</button>
        </form>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
        <div style="background: #f8f9fa; padding: 15px; border-radius: 4px;">
            <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #666;">Driver Actif</h3>
            <p style="margin: 0; font-size: 24px; font-weight: bold;"><?= ucfirst($stats['driver'] ?? 'N/A') ?></p>
        </div>

        <?php if (isset($stats['total_files'])): ?>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 4px;">
                <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #666;">Fichiers</h3>
                <p style="margin: 0; font-size: 24px; font-weight: bold;"><?= $stats['total_files'] ?></p>
            </div>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 4px;">
                <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #666;">Taille</h3>
                <p style="margin: 0; font-size: 24px; font-weight: bold;"><?= $stats['total_size'] ?></p>
            </div>
        <?php endif; ?>

        <?php if (isset($stats['keys_count'])): ?>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 4px;">
                <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #666;">Clés</h3>
                <p style="margin: 0; font-size: 24px; font-weight: bold;"><?= $stats['keys_count'] ?></p>
            </div>
        <?php endif; ?>

        <?php if (isset($stats['memory_usage'])): ?>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 4px;">
                <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #666;">Mémoire</h3>
                <p style="margin: 0; font-size: 24px; font-weight: bold;"><?= $stats['memory_usage'] ?></p>
            </div>
        <?php endif; ?>

        <?php if (isset($stats['hit_rate'])): ?>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 4px;">
                <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #666;">Hit Rate</h3>
                <p style="margin: 0; font-size: 24px; font-weight: bold;"><?= $stats['hit_rate'] ?></p>
            </div>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Métrique</th>
                <th>Valeur</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stats as $key => $value): ?>
                <?php if ($key === 'driver') continue; ?>
                <tr>
                    <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $key))) ?></td>
                    <td><?= htmlspecialchars($value) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
@endsection