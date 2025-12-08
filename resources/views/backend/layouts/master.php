<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars(site_description('DavinciKod Admin is super flexible, powerful, clean &amp; modern responsive bootstrap 5 admin template with unlimited possibilities.')) ?>">
    <meta name="keywords" content="admin template, dashboard template, flat admin template, responsive admin template, web app">
    <meta name="author" content="<?= htmlspecialchars(settings('site_author', 'LathDevinci')) ?>">
    <link rel="icon" href="<?= site_favicon() ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?= site_favicon() ?>" type="image/x-icon">
    <title><?= htmlspecialchars(site_name()) ?> - @yield('title')</title>
    <!-- Google font-->
    <link href="https://fonts.googleapis.com/css?family=Rubik:400,400i,500,500i,700,700i&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900&amp;display=swap" rel="stylesheet">
    <!-- css -->
    @include('backend.layouts.css')

</head>

<body onload="startTime()">
    <!-- loader starts-->
    <div class="loader-wrapper">
        <div class="loader-index"><span></span></div>
        <svg>
            <defs></defs>
            <filter id="goo">
                <fegaussianblur in="SourceGraphic" stddeviation="11" result="blur"></fegaussianblur>
                <fecolormatrix in="blur" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 19 -9" result="goo"> </fecolormatrix>
            </filter>
        </svg>
    </div>
    <!-- loader ends-->

    <!-- tap on top starts-->
    <div class="tap-top"><i data-feather="chevrons-up"></i></div>
    <!-- tap on tap ends-->

    <!-- page-wrapper Start-->
    <div class="page-wrapper compact-wrapper" id="pageWrapper">

        <!-- Header Start -->
        @include('backend.layouts.header')
        <!--Header End  -->

        <!-- Page Body Start-->
        <div class="page-body-wrapper">

            <!-- Page Sidebar Start-->
            @include('backend.layouts.sidebar')
            <!-- Page Sidebar Ends-->

            <div class="page-body">
                <!-- Main Content Start -->
                @yield('content')

                <!-- Main Content End -->
            </div>

            <!-- Footer -->
            @include('backend.layouts.footer')

        </div>
    </div>

    <!-- Script-->
    @include('backend.layouts.script')

    <!-- Flash Messages -->
    @include('backend.inc.alerts')



</body>

</html>