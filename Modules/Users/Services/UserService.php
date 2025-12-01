<?php

namespace Modules\Users\Services;

use App\Core\Contracts\UserProviderInterface;
use Modules\Users\Repositories\UserRepository;
use Modules\RBAC\Services\RbacService;
use App\Core\Container\Container;

class UserService implements UserProviderInterface
{
    protected UserRepository $userRepository;
    protected RbacService $rbacService;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->rbacService = Container::getInstance()->make(RbacService::class);
    }

    public function findById(int $id)
    {
        return $this->userRepository->findById($id);
    }

    public function findByEmail(string $email)
    {
        return $this->userRepository->findByEmail($email);
    }

    public function findByUsername(string $username)
    {
        return $this->userRepository->findByUsername($username);
    }

    public function createUser(array $data)
    {
        // Hash password if present
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $user = $this->userRepository->create($data);

        if (isset($data['roles']) && is_array($data['roles'])) {
            $this->rbacService->syncRoles($user, $data['roles']);
        }

        return $user;
    }

    public function updateUser(int $id, array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $user = $this->userRepository->update($id, $data);

        if ($user && isset($data['roles']) && is_array($data['roles'])) {
            $this->rbacService->syncRoles($user, $data['roles']);
        }

        return $user;
    }

    public function deleteUser(int $id)
    {
        return $this->userRepository->delete($id);
    }

    public function assignRoles(int $userId, array $roles)
    {
        $user = $this->findById($userId);
        if ($user) {
            $this->rbacService->syncRoles($user, $roles);
        }
    }
}
