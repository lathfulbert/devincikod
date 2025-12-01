<?php

namespace Modules\Akpa\Controllers;

use App\Core\Application;

class AkpaController
{
    public function index()
    {
        $app = Application::getInstance();

        // Simulation de données
        $posts = [
            ['id' => 1, 'title' => 'Premier article', 'author' => 'Admin', 'created_at' => '2024-01-15'],
            ['id' => 2, 'title' => 'Deuxième article', 'author' => 'Admin', 'created_at' => '2024-01-16'],
            ['id' => 3, 'title' => 'Troisième article', 'author' => 'Admin', 'created_at' => '2024-01-17'],
        ];

        echo view('akpa/index', [
            'title' => 'Liste des Articles',
            'posts' => $posts
        ]);
    }

    public function create()
    {
        $app = Application::getInstance();

        echo view('akpa/create', [
            'title' => 'Nouvel Article'
        ]);
    }

    public function store()
    {
        $_SESSION['flash']['success'] = 'Article créé avec succès !';
        redirect('/admin/akpa');
    }

    public function edit(array $params = [])
    {
        $app = Application::getInstance();
        $id = $params['id'] ?? null;

        echo view('akpa/edit', [
            'title' => 'Modifier l\'article #' . $id,
            'id' => $id
        ]);
    }

    public function update(array $params = [])
    {
        $_SESSION['flash']['success'] = 'Article mis à jour avec succès !';
        redirect('/admin/akpa');
    }

    public function delete(array $params = [])
    {
        $_SESSION['flash']['success'] = 'Article supprimé avec succès !';
        redirect('/admin/akpa');
    }
}
