<?php
// Vue Dashboard dynamique : affiche tous les widgets disponibles
/** @var array $widgets */
/** @var \Modules\Dashboard\Controllers\DashboardController $user */
?>
@extends('backend.layouts.master')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1>Tableau de bord</h1>
        </div>
    </div>
    @if (!empty($debugAlert))
        {!! $debugAlert !!}
    @endif
    <div class="row">
        @foreach ($widgets as $widget)
            <div class="col-md-4 mb-4">
                {!! $widget !!}
            </div>
        @endforeach
    </div>
</div>
@endsection