@extends('backend.layouts.master')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800">{{ $title }}</h1>
        </div>
        <div class="col-auto">
            <a href="/admin/sms/dashboard" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Liste des Fournisseurs SMS</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- TODO: Afficher la liste des fournisseurs --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
