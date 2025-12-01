<?php

namespace Modules\Users\Repositories;

use Modules\Users\Models\User;

class UserRepository
{
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findByUsername(string $username): ?User
    {
        return User::where('username', $username)->first();
    }

    public function create(array $data): User
    {
        $user = new User();
        foreach ($data as $key => $value) {
            $user->{$key} = $value;
        }
        $user->save();
        return $user;
    }

    public function update(int $id, array $data): ?User
    {
        $user = $this->findById($id);
        if (!$user) return null;

        foreach ($data as $key => $value) {
            $user->{$key} = $value;
        }
        $user->save();
        return $user;
    }

    public function delete(int $id): bool
    {
        $user = $this->findById($id);
        if ($user) {
            return (bool) $user->delete();
        }
        return false;
    }

    public function paginate(int $perPage = 15, int $page = 1)
    {
        // Assuming Model has paginate method or we implement offset/limit
        return User::paginate($perPage, $page);
    }
}
