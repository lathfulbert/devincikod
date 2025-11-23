<?php

namespace App\Models;

use App\Core\Database\Model;
use App\Core\Database\Traits\SoftDeletes;

/**
 * Exemple de Model utilisant le Soft Delete
 */
class ExampleModel extends Model
{
    use SoftDeletes;

    protected static string $table = 'examples';
}
