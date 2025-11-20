<?php

namespace App\Core\Module;

interface ModuleContract
{
    public function getName(): string;
    public function register(): void;
    public function boot(): void;
    public function getRoutes(): array;
}
