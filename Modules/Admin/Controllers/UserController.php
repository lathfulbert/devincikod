<?php

namespace Modules\Admin\Controllers;

use App\Core\Application;
use Modules\Auth\Models\User;
use Modules\RBAC\Models\Role;
use App\Core\Database\Database;

class UserController
{
    public function index()
    {
        $app = Application::getInstance();
        $users = User::query()->orderBy('id', 'DESC')->paginate(10);
        echo $app->view->render('admin/users/index', ['title' => 'Users', 'users' => $users]);
    }

    public function create()
    {
        $app = Application::getInstance();
        $roles = Role::all();
        echo $app->view->render('admin/users/create', ['title' => 'Create User', 'roles' => $roles]);
    }

    public function store()
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $roleIds = $_POST['roles'] ?? [];

        if (empty($username) || empty($password)) {
            // Handle error
            redirect('/admin/users/create');
            exit;
        }

        $user = new User();
        $user->username = $username;
        $user->password = password_hash($password, PASSWORD_BCRYPT);
        $user->save();

        // Assign roles
        if (!empty($roleIds)) {
            $user->roles()->attach($roleIds);
        }

        redirect('/admin/users');
        exit;
    }

    public function edit(array $params = [])
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            redirect('/admin/users');
            return;
        }

        $app = Application::getInstance();
        $user = User::find($id);
        $roles = Role::all();
        // Use getResults() to get the array of Role objects
        $userRoles = $user->roles()->getResults();
        $userRoleIds = array_map(fn($r) => $r->id, $userRoles);

        echo $app->view->render('admin/users/edit', [
            'title' => 'Edit User',
            'user' => $user,
            'roles' => $roles,
            'userRoleIds' => $userRoleIds
        ]);
    }

    public function update(array $params = [])
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            redirect('/admin/users');
            return;
        }

        $user = User::find($id);
        if (!$user) {
            redirect('/admin/users');
            exit;
        }

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $roleIds = $_POST['roles'] ?? [];

        $user->username = $username;
        if (!empty($password)) {
            $user->password = password_hash($password, PASSWORD_BCRYPT);
        }
        $user->save();

        // Update roles
        $user->roles()->sync($roleIds);

        redirect('/admin/users');
        exit;
    }

    public function delete(array $params = [])
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            redirect('/admin/users');
            return;
        }

        $user = User::find($id);
        if ($user) {
            // Detach all roles before deleting
            $user->roles()->detach();
            $user->delete();
        }
        redirect('/admin/users');
        exit;
    }
}
