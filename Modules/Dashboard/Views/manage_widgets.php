<?php /** @var array $widgets */ ?>
@extends('backend.layouts.master')

@section('title', 'Gestion des widgets du dashboard')

@section('content')
<div class="container-fluid">
    <h1>Gestion des widgets du dashboard</h1>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Module</th>
                <th>Classe</th>
                <th>Statut</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($widgets as $widget)
            <tr>
                <td>{{ $widget['name'] }}</td>
                <td>{{ $widget['module'] }}</td>
                <td><code>{{ $widget['class'] }}</code></td>
                <td>
                    @if ($widget['enabled'])
                        <span class="badge bg-success">Activé</span>
                    @else
                        <span class="badge bg-secondary">Désactivé</span>
                    @endif
                </td>
                <td>
                    <form method="POST" action="<?= route('admin.dashboard.widgets.toggle') ?>" style="display:inline">
                           <?= csrf_field() ?>
                        <input type="hidden" name="widget" value="{{ $widget['name'] }}">
                        <button type="submit" class="btn btn-sm btn-outline-{{ $widget['enabled'] ? 'danger' : 'success' }}">
                            {{ $widget['enabled'] ? 'Désactiver' : 'Activer' }}
                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
