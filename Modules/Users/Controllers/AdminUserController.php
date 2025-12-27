<?php

namespace Modules\Users\Controllers;

use App\Core\Application;
use Modules\Users\Models\User;
use Modules\RBAC\Models\Role;
use App\Core\Database\Database;

class AdminUserController
{
    public function index()
    {
        $app = Application::getInstance();
        $users = User::query()->with('roles')->orderBy('id', 'DESC')->paginate(10);
        return view('users/admin/users/index', ['title' => 'Users', 'users' => $users]);
    }

    public function create()
    {
        $app = Application::getInstance();
        $roles = Role::all();
        return view('users/admin/users/create', ['title' => 'Create User', 'roles' => $roles]);
    }

    public function store()
    {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $roleIds = $_POST['roles'] ?? [];

        if (empty($username) || empty($email) || empty($password)) {
            $_SESSION['flash_error'] = 'Username, email et mot de passe sont requis';
            redirect('/admin/users/create');
            exit;
        }

        // Vérifier si l'email existe déjà
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            $_SESSION['flash_error'] = 'Cet email est déjà utilisé';
            redirect('/admin/users/create');
            exit;
        }

        $user = new User();
        $user->username = $username;
        $user->email = $email;
        $user->password = password_hash($password, PASSWORD_BCRYPT);
        $user->is_active = 1;
        $user->save();

        // Assign roles
        if (!empty($roleIds)) {
            $user->roles()->attach($roleIds);
        }

        $_SESSION['flash_success'] = 'Utilisateur créé avec succès !';
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

        return view('users/admin/users/edit', [
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
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $roleIds = $_POST['roles'] ?? [];

        if (empty($username) || empty($email)) {
            $_SESSION['flash_error'] = 'Username et email sont requis';
            redirect('/admin/users/' . $id . '/edit');
            exit;
        }

        // Vérifier si l'email existe déjà (sauf pour cet utilisateur)
        $existingUser = User::where('email', $email)->first();
        if ($existingUser && $existingUser->id != $user->id) {
            $_SESSION['flash_error'] = 'Cet email est déjà utilisé par un autre utilisateur';
            redirect('/admin/users/' . $id . '/edit');
            exit;
        }

        $user->username = $username;
        $user->email = $email;
        if (!empty($password)) {
            $user->password = password_hash($password, PASSWORD_BCRYPT);
        }
        $user->save();

        // Update roles
        $user->roles()->sync($roleIds);

        $_SESSION['flash_success'] = 'Utilisateur modifié avec succès !';
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
            $_SESSION['flash_success'] = 'Utilisateur supprimé avec succès !';
        } else {
            $_SESSION['flash_error'] = 'Utilisateur introuvable';
        }
        redirect('/admin/users');
        exit;
    }
}
