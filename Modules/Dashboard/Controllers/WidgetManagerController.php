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
        $widgets = $registry->getAllWidgets();

        // Récupérer le statut de chaque widget (en dur ou via config plus tard)
        $widgetList = [];
        foreach ($widgets as $widget) {
            $widgetList[] = [
                'name' => $widget->getName(),
                'module' => (new \ReflectionClass($widget))->getNamespaceName(),
                'enabled' => $widget->isEnabled(),
                'class' => get_class($widget)
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
        header('Location: /admin/dashboard/widgets');
        exit;
    }
}
