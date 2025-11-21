@extends('admin.layout')

@section('content')
<div class="header">
    <h1>Dashboard</h1>
</div>

<div class="card">
    <h2>Welcome to the Admin Panel</h2>
    <p>Use the sidebar to manage users, roles, and permissions.</p>
</div>

<div style="display: flex; gap: 20px;">
    <div class="card" style="flex: 1;">
        <h3>Users</h3>
        <p>Manage system users.</p>
        <a href="<?= url('/admin/users') ?>" class="btn btn-primary">Go to Users</a>
    </div>
    <div class="card" style="flex: 1;">
        <h3>Roles</h3>
        <p>Manage user roles.</p>
        <a href="<?= url('/admin/roles') ?>" class="btn btn-primary">Go to Roles</a>
    </div>
    <div class="card" style="flex: 1;">
        <h3>Permissions</h3>
        <p>Manage access permissions.</p>
        <a href="<?= url('/admin/permissions') ?>" class="btn btn-primary">Go to Permissions</a>
    </div>

    <div class="card" style="flex: 1;">
        <h3>Modules</h3>
        <p>Manage Modules</p>
        <a href="<?= url('/admin/modules') ?>" class="btn btn-primary">Go to Permissions</a>
    </div>
</div>
@endsection