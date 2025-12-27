<?php

require 'vendor/autoload.php';
require 'Core/Support/helpers.php';

$app = new \App\Core\Application(__DIR__);
$app->boot();

echo "=== Test du SidebarService ===\n\n";

// Récupérer les items du menu
$menuItems = \App\Core\View\SidebarService::getItems();

echo "Total Menu Items: " . count($menuItems) . "\n\n";

foreach ($menuItems as $index => $item) {
    echo "[$index] Type: {$item['type']} - ";

    if ($item['type'] === 'separator') {
        echo "Label: {$item['label']}\n";
    } elseif ($item['type'] === 'link') {
        echo "Title: {$item['title']} - URL: {$item['url']}\n";
    } elseif ($item['type'] === 'dropdown') {
        echo "Title: {$item['title']} - Icon: {$item['icon']}\n";
        if (isset($item['children'])) {
            foreach ($item['children'] as $child) {
                echo "    ↳ {$child['title']} - {$child['url']}\n";
            }
        }
    }
}

echo "\n=== Recherche des menus Users ===\n";
$usersMenus = array_filter($menuItems, function ($item) {
    return isset($item['title']) && stripos($item['title'], 'utilisateur') !== false;
});

if (empty($usersMenus)) {
    echo "❌ Aucun menu Users trouvé!\n";
} else {
    echo "✓ " . count($usersMenus) . " menu(s) Users trouvé(s):\n";
    foreach ($usersMenus as $menu) {
        print_r($menu);
    }
}
