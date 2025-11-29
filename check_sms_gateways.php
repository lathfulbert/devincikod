<?php

require 'vendor/autoload.php';

$app = new App\Core\Application(__DIR__);
$app->boot();

$gateways = Modules\Settings\Models\SmsGateway::all();

echo "Gateways SMS disponibles:\n";
echo "=========================\n\n";

foreach ($gateways as $gw) {
    echo "- {$gw->name} [{$gw->provider_code}]\n";
    echo "  Active: " . ($gw->is_active ? 'Oui' : 'Non') . "\n";
    echo "  Default: " . ($gw->is_default ? 'Oui' : 'Non') . "\n\n";
}
