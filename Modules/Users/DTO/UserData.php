<?php

namespace Modules\Users\DTO;

class UserData
{
    public ?int $id = null;
    public string $username;
    public string $email;
    public ?string $password = null;
    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $phone = null;
    public bool $isActive = true;
    public array $roles = [];

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->id = $data['id'] ?? null;
        $dto->username = $data['username'] ?? '';
        $dto->email = $data['email'] ?? '';
        $dto->password = $data['password'] ?? null;
        $dto->firstName = $data['first_name'] ?? null;
        $dto->lastName = $data['last_name'] ?? null;
        $dto->phone = $data['phone'] ?? null;
        $dto->isActive = $data['is_active'] ?? true;
        $dto->roles = $data['roles'] ?? [];
        return $dto;
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'phone' => $this->phone,
            'is_active' => $this->isActive,
        ], fn($v) => !is_null($v));
    }
}
