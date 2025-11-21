<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin' ?> - SunuFramework</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #333;
            color: #fff;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .sidebar a {
            color: #ccc;
            text-decoration: none;
            padding: 10px;
            margin-bottom: 5px;
            border-radius: 4px;
        }

        .sidebar a:hover {
            background: #444;
            color: #fff;
        }

        .sidebar .brand {
            font-size: 1.5em;
            font-weight: bold;
            margin-bottom: 30px;
            color: #fff;
        }

        .content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f4f4f4;
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f8f9fa;
        }

        .btn {
            padding: 8px 12px;
            border-radius: 4px;
            text-decoration: none;
            color: #fff;
            display: inline-block;
            cursor: pointer;
            border: none;
            font-size: 14px;
        }

        .btn-primary {
            background-color: #007bff;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .btn-success {
            background-color: #28a745;
        }

        .btn-sm {
            padding: 4px 8px;
            font-size: 12px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .checkbox-group {
            margin-top: 5px;
        }

        .checkbox-group label {
            display: inline-block;
            margin-right: 15px;
            font-weight: normal;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="brand">Sunu Admin</div>
        <a href="<?= url('/admin/dashboard') ?>">Dashboard</a>
        <a href="<?= url('/admin/users') ?>">Users</a>
        <a href="<?= url('/admin/roles') ?>">Roles</a>
        <a href="<?= url('/admin/permissions') ?>">Permissions</a>
        <a href="<?= url('/admin/modules') ?>">Modules</a>
        <a href="<?= url('/admin/cache') ?>">Cache</a>
        <div style="margin-top: auto;">
            <a href="<?= url('/') ?>">Back to Site</a>
            <a href="<?= url('/logout') ?>">Logout</a>
        </div>
    </div>
    <div class="content">
        @yield('content')
    </div>
</body>

</html>