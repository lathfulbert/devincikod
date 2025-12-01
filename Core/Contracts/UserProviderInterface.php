<?php

namespace App\Core\Contracts;

interface UserProviderInterface
{
    public function findById(int $id);
    public function findByEmail(string $email);
    public function findByUsername(string $username);
    public function createUser(array $data);
    public function updateUser(int $id, array $data);
    public function deleteUser(int $id);
    public function assignRoles(int $userId, array $roles);
}
