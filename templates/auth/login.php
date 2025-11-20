@extends('layouts.app')

@section('title') Login @endsection

@section('content')
<div style="max-width: 400px; margin: 50px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <h2 style="text-align: center; margin-bottom: 20px;">Login</h2>

    @if (isset($error))
    <div class="alert alert-danger">
        {{ $error }}
    </div>
    @endif

    <form method="POST" action="<?= url('/login') ?>">
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 5px;">Username (admin)</label>
            <input type="text" name="username" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
        </div>
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 5px;">Password (password)</label>
            <input type="password" name="password" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
        </div>
        <button type="submit" style="width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Login</button>
    </form>
</div>
@endsection