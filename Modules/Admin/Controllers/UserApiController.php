<?php

namespace Modules\Admin\Controllers;

use App\Core\Application;
use Modules\Users\Models\User;

class UserApiController
{
    /**
     * DataTables AJAX endpoint for users list.
     */
    public function datatable()
    {
        // Define searchable and orderable columns
        $columns = ['id', 'username', 'email', 'created_at'];

        // Build DataTables response
        $data = User::query()
            ->orderBy('id', 'DESC')
            ->datatables($columns)
            ->make();

        // Return JSON
        header('Content-Type: application/json');
        return json_encode($data);
        exit;
    }

    /**
     * Example: DataTables with filters.
     */
    public function datatableFiltered()
    {
        $columns = ['id', 'username', 'email', 'created_at'];

        $data = User::query()
            ->where('active', 1) // Add custom filters
            ->orderBy('id', 'DESC')
            ->datatables($columns)
            ->make();

        header('Content-Type: application/json');
        return json_encode($data);
        exit;
    }
}
