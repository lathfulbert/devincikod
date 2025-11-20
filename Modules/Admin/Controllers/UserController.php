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
        $users = User::all();
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
            $db = Database::getInstance();
            foreach ($roleIds as $roleId) {
                $db->query("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)", [$user->id, $roleId]);
            }
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
        $userRoles = $user->roles();
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

        // Update roles (simple delete and re-insert for now)
        $db = Database::getInstance();
        $db->query("DELETE FROM user_roles WHERE user_id = ?", [$user->id]);
        
        if (!empty($roleIds)) {
            foreach ($roleIds as $roleId) {
                $db->query("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)", [$user->id, $roleId]);
            }
        }

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
            $db = Database::getInstance();
            $db->query("DELETE FROM user_roles WHERE user_id = ?", [$user->id]);
            $user->delete();
        }
        redirect('/admin/users');
        exit;
    }
}
