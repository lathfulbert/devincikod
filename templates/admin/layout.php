<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SunuFramework - Administration">
    <meta name="keywords" content="admin, dashboard">
    <meta name="author" content="SunuFramework">

    <title><?= $title ?? 'Dashboard' ?> - SunuFramework Admin</title>

    <!-- Favicon -->
    <link rel="icon" href="<?= url('/assets/images/logo/favicon.png') ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?= url('/assets/images/logo/favicon.png') ?>" type="image/x-icon">

    <!-- Google font-->
    <link href="https://fonts.googleapis.com/css?family=Rubik:400,400i,500,500i,700,700i&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900&amp;display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" type="text/css" href="<?= url('/assets/css/fontawesome.css') ?>">

    <!-- Ico-font -->
    <link rel="stylesheet" type="text/css" href="<?= url('/assets/css/vendors/icofont.css') ?>">

    <!-- Themify icon -->
    <link rel="stylesheet" type="text/css" href="<?= url('/assets/css/vendors/themify.css') ?>">

    <!-- Flag icon -->
    <link rel="stylesheet" type="text/css" href="<?= url('/assets/css/vendors/flag-icon.css') ?>">

    <!-- Feather icon -->
    <link rel="stylesheet" type="text/css" href="<?= url('/assets/css/vendors/feather-icon.css') ?>">

    <!-- Bootstrap css -->
    <link rel="stylesheet" type="text/css" href="<?= url('/assets/css/vendors/bootstrap.css') ?>">

    <!-- App css -->
    <link rel="stylesheet" type="text/css" href="<?= url('/assets/css/style.css') ?>">
    <link id="color" rel="stylesheet" href="<?= url('/assets/css/color-1.css') ?>" media="screen">

    <!-- Responsive css -->
    <link rel="stylesheet" type="text/css" href="<?= url('/assets/css/responsive.css') ?>">

    <!-- DataTables (si nécessaire) -->
    <?php if (isset($datatable) && $datatable): ?>
        <link rel="stylesheet" type="text/css" href="<?= url('/assets/css/vendors/dataTables.bootstrap5.css') ?>">
    <?php endif; ?>

    <!-- Vite Assets -->
    <?= vite('resources/css/app.css') ?>
    <?= vite('resources/js/app.js') ?>

    <!-- Styles additionnels par page -->
    @yield('styles')
</head>

<body>
    <!-- Page Loader Start -->
    <div class="loader-wrapper">
        <div class="loader"></div>
    </div>
    <!-- Page Loader End -->

    <!-- Page Body Start -->
    <div class="page-wrapper compact-wrapper" id="pageWrapper">

        <!-- Page Header Start -->
        <div class="page-header">
            <div class="header-wrapper row m-0">
                <!-- Logo & Toggle -->
                <div class="header-logo-wrapper col-auto p-0">
                    <div class="logo-wrapper">
                        <a href="<?= url('/admin/dashboard') ?>">
                            <h4 class="mb-0" style="color: #7366ff; font-weight: 700;">SunuFramework</h4>
                        </a>
                    </div>
                    <div class="toggle-sidebar">
                        <i class="status_toggle middle sidebar-toggle" data-feather="align-center"></i>
                    </div>
                </div>

                <!-- Search & Right Nav -->
                <div class="nav-right col-xxl-7 col-xl-6 col-md-7 col-8 pull-right right-header p-0 ms-auto">
                    <ul class="nav-menus">
                        <!-- Notifications -->
                        <li class="onhover-dropdown">
                            <div class="notification-box">
                                <i data-feather="bell"></i>
                                <span class="badge rounded-pill badge-secondary">3</span>
                            </div>
                            <ul class="notification-dropdown onhover-show-div">
                                <li>
                                    <i data-feather="shopping-bag"></i>
                                    <h6 class="f-18 mb-0">Notifications</h6>
                                </li>
                                <li>
                                    <p>
                                        <i class="fa fa-circle-o me-3 font-primary"></i>Nouvelle permission ajoutée
                                        <span class="pull-right">Il y a 2 heures</span>
                                    </p>
                                </li>
                                <li>
                                    <p>
                                        <i class="fa fa-circle-o me-3 font-success"></i>Cache vidé avec succès
                                        <span class="pull-right">Il y a 5 heures</span>
                                    </p>
                                </li>
                                <li class="text-center">
                                    <a href="javascript:void(0)" class="btn btn-primary">Voir toutes</a>
                                </li>
                            </ul>
                        </li>

                        <!-- Profile -->
                        <li class="profile-nav onhover-dropdown p-0 me-0">
                            <div class="media profile-media">
                                <img class="b-r-10" src="<?= url('/assets/images/dashboard/profile.jpg') ?>" alt="" style="width: 35px; height: 35px; object-fit: cover;">
                                <div class="media-body">
                                    <span><?= $_SESSION['user']['username'] ?? 'Administrateur' ?></span>
                                    <p class="mb-0 font-roboto">Admin <i class="middle fa fa-angle-down"></i></p>
                                </div>
                            </div>
                            <ul class="profile-dropdown onhover-show-div">
                                <li>
                                    <a href="<?= url('/admin/profile') ?>">
                                        <i data-feather="user"></i><span>Profil</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= url('/admin/settings') ?>">
                                        <i data-feather="settings"></i><span>Paramètres</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= url('/logout') ?>">
                                        <i data-feather="log-out"></i><span>Déconnexion</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- Page Header End -->

        <!-- Page Body Start -->
        <div class="page-body-wrapper">
            <!-- Sidebar Start -->
            <div class="sidebar-wrapper" sidebar-layout="stroke-svg">
                <div>
                    <div class="logo-wrapper">
                        <a href="<?= url('/admin/dashboard') ?>">
                            <h4 class="mb-0" style="color: #7366ff; font-weight: 700;">Sunu</h4>
                        </a>
                        <div class="back-btn">
                            <i class="fa fa-angle-left"></i>
                        </div>
                        <div class="toggle-sidebar">
                            <i class="status_toggle middle sidebar-toggle" data-feather="grid"></i>
                        </div>
                    </div>
                    <div class="logo-icon-wrapper">
                        <a href="<?= url('/admin/dashboard') ?>">
                            <!-- Sidebar End -->

                            <!-- Page Content Start -->
                            <div class="page-body">
                                <!-- Container-fluid Start -->
                                <div class="container-fluid">
                                    @yield('content')
                                </div>
                                <!-- Container-fluid End -->
                            </div>
                            <!-- Page Content End -->

                            <!-- Footer Start -->
                            <footer class="footer">
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-md-6 p-0 footer-copyright">
                                            <p class="mb-0">Copyright © 2024 SunuFramework. Tous droits réservés.</p>
                                        </div>
                                        <div class="col-md-6 p-0">
                                            <p class="heart mb-0 text-end">Développé avec <i class="fa fa-heart font-danger"></i></p>
                                        </div>
                                    </div>
                                </div>
                            </footer>
                            <!-- Footer End -->
                    </div>
                    <!-- Page Body End -->
                </div>
                <!-- Page Body End -->

                <!-- jQuery -->
                <script src="<?= url('/assets/js/jquery-3.5.1.min.js') ?>"></script>

                <!-- Bootstrap -->
                <script src="<?= url('/assets/js/bootstrap/bootstrap.bundle.min.js') ?>"></script>

                <!-- Feather Icons -->
                <script src="<?= url('/assets/js/icons/feather-icon/feather.min.js') ?>"></script>
                <script src="<?= url('/assets/js/icons/feather-icon/feather-icon.js') ?>"></script>

                <!-- Sidebar -->
                <script src="<?= url('/assets/js/sidebar-menu.js') ?>"></script>

                <!-- Config -->
                <script src="<?= url('/assets/js/config.js') ?>"></script>

                <!-- DataTables (si nécessaire) -->
                <?php if (isset($datatable) && $datatable): ?>
                    <script src="<?= url('/assets/js/datatable/datatables/jquery.dataTables.min.js') ?>"></script>
                    <script src="<?= url('/assets/js/datatable/datatables/datatable.custom.js') ?>"></script>
                <?php endif; ?>

                <!-- Custom Script -->
                <script src="<?= url('/assets/js/script.js') ?>"></script>

                <!-- Scripts additionnels par page -->
                @yield('scripts')

                <!-- Theme Customizer (optionnel) -->
                <!-- <script src="<?= url('/assets/js/theme-customizer/customizer.js') ?>"></script> -->
</body>

</html>