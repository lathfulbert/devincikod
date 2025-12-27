<?php

namespace Modules\Blog\Controllers;

use App\Core\Application;

class BlogController
{
    public function index()
    {
        $posts = [
            ['id' => 1, 'title' => 'Welcome to the Blog', 'content' => 'This is the first post.'],
            ['id' => 2, 'title' => 'Modular PHP', 'content' => 'Building a modular framework is fun.']
        ];

        return view('blog/index', ['title' => 'Blog Module', 'posts' => $posts]);
    }
}
