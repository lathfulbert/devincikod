<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | <?= config('app.name') ?></title>
    <link rel="icon" href="<?= asset('assets/images/favicon.png') ?>" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css?family=Rubik:400,400i,500,500i,700,700i&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="<?= asset('assets/css/fontawesome.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= asset('assets/css/vendors/icofont.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= asset('assets/css/vendors/themify.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= asset('assets/css/vendors/feather-icon.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= asset('assets/css/vendors/bootstrap.css') ?>">
    <link id="color" rel="stylesheet" href="<?= asset('assets/css/color-1.css') ?>" media="screen">
    <link rel="stylesheet" type="text/css" href="<?= asset('assets/css/responsive.css') ?>">
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 p-0">
                <div class="login-card login-dark">
                    <div>
                        <div>
                            <a class="logo text-start" href="<?= url('/') ?>">
                                <img class="img-fluid for-light" src="<?= asset('assets/images/logo/logo.png') ?>" alt="logo">
                            </a>
                        </div>
                        <div class="login-main">
                            <?php component('alerts'); ?>

                            <form class="theme-form" action="<?= url('/forgot-password') ?>" method="POST">
                                <?= csrf_field() ?>

                                <h4>Forgot Password?</h4>
                                <p class="text-muted">Enter your email address and we'll send you a link to reset your password.</p>

                                <div class="form-group">
                                    <label class="col-form-label">Email Address</label>
                                    <input class="form-control" type="email" name="email" required placeholder="your@email.com">
                                </div>

                                <div class="form-group mb-0 d-grid">
                                    <button class="btn btn-primary btn-block" type="submit">Send Reset Link</button>
                                </div>

                                <div class="mt-4 text-center">
                                    <p class="mb-0">Remember your password?
                                        <a class="ms-2" href="<?= url('/login') ?>">Sign In</a>
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= asset('assets/js/bootstrap/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= asset('assets/js/icons/feather-icon/feather.min.js') ?>"></script>
    <script src="<?= asset('assets/js/icons/feather-icon/feather-icon.js') ?>"></script>
</body>

</html>