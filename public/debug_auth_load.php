<?php
require_once __DIR__ . '/../vendor/autoload.php';

try {
    $class = "Modules\\Auth\\AuthModule";
    if (class_exists($class)) {
        echo "Class $class exists.\n";
        $module = new $class();
        echo "Module instantiated successfully.\n";
        echo "Name: " . $module->getName() . "\n";
    } else {
        echo "Class $class does NOT exist.\n";
    }
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
