@extends('backend.layouts.master')

@section('title', 'Edit Profile')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>My Profile</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/admin/dashboard') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item active">Profile</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php component('alerts'); ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php if ($user->avatar): ?>
                            <img src="<?= url($user->avatar) ?>" alt="Avatar" class="rounded-circle" width="150" height="150" style="object-fit: cover;">
                        <?php else: ?>
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 150px; height: 150px; font-size: 48px;">
                                <?= strtoupper(substr($user->username, 0, 2)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h4><?= htmlspecialchars($user->username) ?></h4>
                    <p class="text-muted"><?= htmlspecialchars($user->email) ?></p>

                    <div class="mt-4">
                        <a href="<?= url('/admin/profile/change-password') ?>" class="btn btn-warning btn-block">
                            <i data-feather="lock"></i> Change Password
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Edit Profile Information</h5>
                </div>
                <div class="card-body">
                    <form action="<?= url('/admin/profile/update') ?>" method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user->username) ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user->email) ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($user->first_name ?? '') ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($user->last_name ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="avatar" class="form-label">Profile Picture</label>
                            <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                            <small class="text-muted">Allowed formats: JPG, PNG, GIF. Max size: 2MB</small>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= url('/admin/dashboard') ?>" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="save"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
feather.replace();
</script>
@endsection
