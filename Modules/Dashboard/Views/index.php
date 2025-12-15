<?php
// Vue Dashboard dynamique : affiche tous les widgets disponibles

/** @var array $widgets */
/** @var \Modules\Dashboard\Controllers\DashboardController $user */
?>
@extends('backend.layouts.master')

@section('title', 'Dashboard')


@section('content')


  <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-6">
                    <h3>Default </h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.default_dashboard') }}"> <svg class="stroke-icon">
                                    <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                                </svg></a></li>
                        <li class="breadcrumb-item">Dashboard</li>
                        <li class="breadcrumb-item active">Default </li>
                    </ol>
                </div>
            </div>
        </div>
    </div><!-- Container-fluid starts-->




<div class="container-fluid default-dashboard">

   
    <div class="row widget-grid">
        @foreach ($widgets as $widget)
        <div class="col-xxl-6 col-sm-6 box-col-6">
 {!! $widget !!}
             
           
        </div>
        @endforeach
    </div>
</div>
@endsection