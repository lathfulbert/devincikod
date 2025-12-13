<?php
$p1 = __DIR__ . '/../resources/views/backend/dashboard.php';
$p2 = 'C:\\laragon\\www\\sunuframework2/resources/views/backend/dashboard.php';

$c1 = file_get_contents($p1);
$c2 = file_get_contents($p2);

echo "p1: [$p1]\nmd5: " . md5($p1) . "\ncontent md5: " . md5($c1) . "\n\n";
echo "p2: [$p2]\nmd5: " . md5($p2) . "\ncontent md5: " . md5($c2) . "\n\n";

if ($c1 === $c2) echo "CONTENTS IDENTICAL\n"; else echo "CONTENTS DIFFER\n";
