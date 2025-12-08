<!DOCTYPE html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SunuFramework - Modern PHP Framework">
    <link rel="icon" href="<?= asset('assets/images/favicon.png') ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?= asset('assets/images/favicon.png') ?>" type="image/x-icon">
    <title><?= $title ?? 'Error' ?> | SunuFramework</title>

    <!-- Google font-->
    <link href="https://fonts.googleapis.com/css?family=Rubik:400,400i,500,500i,700,700i&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900&amp;display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="<?= asset('assets/css/vendors/bootstrap.css') ?>">
    <!-- Feather icon CSS -->
    <link rel="stylesheet" type="text/css" href="<?= asset('assets/css/vendors/feather-icon.css') ?>">
    <!-- App CSS -->
    <link rel="stylesheet" type="text/css" href="<?= asset('assets/css/style.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= asset('assets/css/responsive.css') ?>">

    <style>
        .error-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .error-wrapper .container {
            text-align: center;
            padding: 40px 20px;
        }
        .error-wrapper svg {
            width: 300px;
            height: 300px;
            margin: 0 auto 30px;
        }
        .error-wrapper h1 {
            font-size: 120px;
            font-weight: 700;
            color: #fff;
            margin: 0;
            line-height: 1;
        }
        .error-wrapper h3 {
            font-size: 32px;
            font-weight: 500;
            color: #fff;
            margin: 20px 0;
        }
        .error-wrapper .sub-content {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.9);
            margin: 20px auto 40px;
            line-height: 1.6;
        }
        .error-wrapper .btn {
            padding: 15px 40px;
            font-size: 16px;
            border-radius: 50px;
            text-transform: uppercase;
            font-weight: 500;
            letter-spacing: 1px;
        }
        .error-code {
            position: relative;
            display: inline-block;
        }
        .error-code::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.5; }
            50% { transform: translate(-50%, -50%) scale(1.1); opacity: 0.8; }
        }
    </style>
</head>
<body>
    <!-- tap on top starts-->
    <div class="tap-top"><i data-feather="chevrons-up"></i></div>
    <!-- tap on tap ends-->

    <!-- page-wrapper Start-->
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
        <div class="error-wrapper">
            <?= $content ?>
        </div>
    </div>
    <!-- page-wrapper Ends-->

    <!-- Scripts -->
    <script src="<?= asset('assets/js/jquery.min.js') ?>"></script>
    <script src="<?= asset('assets/js/bootstrap/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= asset('assets/js/icons/feather-icon/feather.min.js') ?>"></script>
    <script src="<?= asset('assets/js/icons/feather-icon/feather-icon.js') ?>"></script>
    <script>
        feather.replace();
    </script>
</body>
</html>
