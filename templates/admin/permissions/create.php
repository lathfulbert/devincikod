@extends('admin.layout')

@section('content')
<div class="header">
    <h1>Create Permission</h1>
    <a href="<?= url('/admin/permissions') ?>" class="btn btn-primary">Back</a>
</div>

<div class="card">
    <form action="<?= url('/admin/permissions') ?>" method="POST">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="slug">Slug</label>
            <input type="text" id="slug" name="slug" required>
        </div>
        <button type="submit" class="btn btn-success">Create Permission</button>
    </form>
</div>
@endsection