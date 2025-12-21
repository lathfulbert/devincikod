@extends('backend.layouts.master')

@section('title', $title ?? 'Tarification SMS')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800">{{ $title ?? 'Tarification SMS' }}</h1>
        </div>
        <div class="col-auto">
            <a href="/admin/sms/dashboard" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Grille de Tarification</h6>
        </div>
        <div class="card-body">
            {{-- TODO: Afficher la grille de tarification --}}
        </div>
    </div>
</div>
@endsection
