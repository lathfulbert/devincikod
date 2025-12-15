<?php
namespace Modules\Dashboard\Controllers;

use App\Core\Widget\WidgetRegistry;

class WidgetManagerController
{
    public function index()
    {
        // Découvrir tous les widgets
        $registry = WidgetRegistry::getInstance();
        $registry->discoverAllWidgets();

        // Charger le statut depuis la config
        $configPath = __DIR__ . '/../../../config/widgets_status.php';
        $statusConfig = file_exists($configPath) ? include $configPath : [];

        // Récupérer TOUS les widgets (activés ou non)
        $all = (new \ReflectionObject($registry))->getProperty('widgets');
        $all->setAccessible(true);
        $widgets = $all->getValue($registry);

        $widgetList = [];
        foreach ($widgets as $widgetName => $meta) {
            $class = $meta['class'];
            $instance = $meta['instance'] ?? new $class();
            $enabled = array_key_exists($widgetName, $statusConfig)
                ? (bool)$statusConfig[$widgetName]
                : $instance->isEnabled();
            $widgetList[] = [
                'name' => $widgetName,
                'module' => (new \ReflectionClass($instance))->getNamespaceName(),
                'enabled' => $enabled,
                'class' => $class
            ];
        }

        echo view('dashboard/manage_widgets', ['widgets' => $widgetList]);
    }

    public function toggle()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Méthode non autorisée');
        }
        $widget = $_POST['widget'] ?? null;
        if (!$widget) {
            http_response_code(400);
            exit('Widget non spécifié');
        }
        $configPath = __DIR__ . '/../../../config/widgets_status.php';
        $statusConfig = file_exists($configPath) ? include $configPath : [];
        $current = isset($statusConfig[$widget]) ? (bool)$statusConfig[$widget] : true;
        $statusConfig[$widget] = !$current;
        // Sauvegarder le fichier
        $export = "<?php\nreturn " . var_export($statusConfig, true) . ";\n";
        file_put_contents($configPath, $export);
       // header('Location: /admin/dashboard/widgets');
        redirect('/admin/dashboard/widgets');
        exit;
    }
}
