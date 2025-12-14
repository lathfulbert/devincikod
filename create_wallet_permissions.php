<?php

require_once 'bootstrap.php';

// Permissions à créer pour le wallet
$permissions = [
    ['name' => 'Wallet Dashboard', 'slug' => 'wallet.dashboard', 'description' => 'Accès au dashboard wallet avec statistiques'],
    ['name' => 'Manage Wallets', 'slug' => 'wallet.manage', 'description' => 'Gérer la liste des wallets'],
    ['name' => 'Topup Wallet', 'slug' => 'wallet.topup', 'description' => 'Recharger son propre wallet'],
    ['name' => 'View Wallet Requests', 'slug' => 'wallet.requests.view', 'description' => 'Voir ses demandes de recharge'],
    ['name' => 'Manage Wallet Requests', 'slug' => 'wallet.requests.manage', 'description' => 'Gérer les demandes de recharge (Admin)']
];

foreach ($permissions as $perm) {
    try {
        $existing = \Modules\RBAC\Models\Permission::where('slug', $perm['slug'])->first();
        if (!$existing) {
            \Modules\RBAC\Models\Permission::create([
                'name' => $perm['name'],
                'slug' => $perm['slug'],
                'description' => $perm['description']
            ]);
            echo 'Créé: ' . $perm['slug'] . PHP_EOL;
        } else {
            echo 'Existe déjà: ' . $perm['slug'] . PHP_EOL;
        }
    } catch (Exception $e) {
        echo 'Erreur pour ' . $perm['slug'] . ': ' . $e->getMessage() . PHP_EOL;
    }
}