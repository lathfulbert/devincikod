<?php

namespace Modules\Demo\Controllers;

use App\Core\Application;

class DemoController
{
    public function index()
    {
        $app = Application::getInstance();
        echo $app->view->render('demo/index', ['title' => 'Demo Module']);
    }
    
    public function templateDemo()
    {
        $app = Application::getInstance();
        
        $data = [
            'title' => 'Template Engine Demo',
            'username' => 'John Doe',
            'isAdmin' => true,
            'items' => ['Item 1', 'Item 2', 'Item 3', 'Item 4']
        ];
        
        echo $app->view->render('demo/template-demo', $data);
    }
}
