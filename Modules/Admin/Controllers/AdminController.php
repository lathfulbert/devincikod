<?php

namespace Modules\Admin\Controllers;

use App\Core\Application;

class AdminController
{
    public function index()
    {
        $app = Application::getInstance();
        echo $app->view->render('admin/dashboard', ['title' => 'Admin Dashboard']);
    }
}
