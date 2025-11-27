<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="DavinciKod Admin is super flexible, powerful, clean &amp; modern responsive bootstrap 5 admin template with unlimited possibilities.">
  <meta name="keywords" content="admin template, DavinciKod Admin template, dashboard template, flat admin template, responsive admin template, web app">
  <meta name="author" content="LathDevinci">
  <link rel="icon" href="{{ asset('assets/images/favicon.png') }}" type="image/x-icon">
  <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" type="image/x-icon">
  <title>@yield('title') | Cuba - Premium Admin Template By LathDevinci</title>
  <!-- Google font-->
  <link href="https://fonts.googleapis.com/css?family=Rubik:400,400i,500,500i,700,700i&amp;display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900&amp;display=swap" rel="stylesheet">
  @include('backend.layouts.auth.css')

  <?= vite('resources/css/app.css') ?>
  <?= vite('resources/js/app.js') ?>
</head>

<body>
  @yield('content')
  @include('backend.layouts.auth.scripts')

  <!-- Flash Messages -->
  @include('backend.inc.alerts')
</body>

</html>