<?php

namespace App\Core\Database;

abstract class Seeder
{
    /**
     * Run the database seeder.
     *
     * @return void
     */
    abstract public function run(): void;
}
