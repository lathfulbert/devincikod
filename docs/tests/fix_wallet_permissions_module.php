<?php
require_once __DIR__ . '/bootstrap.php';

$db = \App\Core\Database\Database::getInstance();

// Permissions Wallet à garder et rattacher au module 'wallet'
$walletPerms = [
    'access.wallet',
    'wallet.dashboard',
    'wallet.manage',
    'wallet.topup',
    'wallet.requests.view',
    'wallet.requests.manage'
];

foreach ($walletPerms as $slug) {
    $db->query('UPDATE permissions SET module_slug = ? WHERE slug = ?', ['wallet', $slug]);
    echo "✓ module_slug=wallet pour $slug\n";
}

// Permissions Wallet à supprimer définitivement
$obsolete = [
    'wallet.create',
    'wallet.debit',
    'wallet.credit',
    'wallet.history.view',
    'wallet.settings.manage',
    'wallet.view',
    'settings.wallet.manage'
];

foreach ($obsolete as $slug) {
    $db->query('DELETE FROM permissions WHERE slug = ?', [$slug]);
    echo "✗ supprimée : $slug\n";
}

echo "\nVérification finale :\n";
$perms = $db->query('SELECT id, name, slug, module_slug FROM permissions WHERE slug LIKE ?', ['%wallet%'])->fetchAll();
foreach ($perms as $p) {
    echo $p['id'] . ': ' . $p['name'] . ' (' . $p['slug'] . ') | module_slug=' . ($p['module_slug'] ?? 'NULL') . "\n";
}
echo "\nNettoyage et rattachement terminés.\n";
