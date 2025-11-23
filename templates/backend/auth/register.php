@extends('backend.layouts.auth.master')

@section('title', 'Register Simple')

@section('css')
@endsection

@section('content')
<div class="container-fluid p-0">
    <div class="row m-0">
        <div class="col-12 p-0">
            <div class="login-card login-dark">

                <?php component('alert') ?>

                <div>
                    <div><a class="logo" href="{{ route('admin.default_dashboard') }}"><img class="img-fluid for-light"
                                src="{{ asset('assets/images/logo/logo.png') }}" alt="looginpage"><img
                                class="img-fluid for-dark" src="{{ asset('assets/images/logo/logo_dark.png') }}"
                                alt="looginpage"></a></div>
                    <div class="login-main create-account">
                        <form class="theme-form" method="POST" action="<?= url('/register') ?>">
                            <?= csrf_field() ?>
                            <h4>Create your account</h4>
                            <p>Enter your personal details to create account</p>

                            <div class="form-group">
                                <label class="col-form-label pt-0">Nom d'utilisateur *</label>
                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <input name="username"
                                            id="username" class="form-control <?= has_error('username') ? 'is-invalid' : '' ?>" type="text" required=""
                                            placeholder="Username" value="<?= escape(old('username')) ?>">
                                    </div>
                                    <div class="col-sm-6">
                                        <input id="email" name="email" class="form-control <?= has_error('email') ? 'is-invalid' : '' ?>" type="text" required=""
                                            placeholder="Votre Adresse Email" value="<?= escape(old('email')) ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-form-label pt-0">Your Name</label>
                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <input name="first_name"
                                            id="first_name" class="form-control <?= has_error('first_name') ? 'is-invalid' : '' ?>" type="text" required=""
                                            placeholder="First name" value="<?= escape(old('first_name')) ?>">
                                    </div>
                                    <div class="col-sm-6">
                                        <input name="last_name"
                                            id="last_name" class="form-control <?= has_error('last_name') ? 'is-invalid' : '' ?>" type="text" required=""
                                            placeholder="Last name" value="<?= escape(old('last_name')) ?>">
                                    </div>
                                </div>
                            </div>


                            <div class="form-group">
                                <label class="col-form-label pt-0">Password</label>
                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <input name="password"
                                            id="password" class="form-control <?= has_error('password') ? 'is-invalid' : '' ?>" type="password" required=""
                                            placeholder="Minimum 8 caractères" value="<?= escape(old('password')) ?>">
                                        <div class="show-hide"><span class="show"></span></div>
                                    </div>
                                    <div class="col-sm-6">
                                        <input name="password_confirmation"
                                            id="password_confirmation" class="form-control <?= has_error('password_confirmation') ? 'is-invalid' : '' ?>" type="password" required=""
                                            placeholder="Confirmer le mot de passe *">
                                        <div class="show-hide"><span class="show"></span></div>
                                    </div>
                                </div>
                            </div>


                            <div class="form-group mb-0">
                                <div class="form-check">
                                    <input class="checkbox-primary form-check-input" name="terms" id="checkbox1" type="checkbox">
                                    <label class="text-muted form-check-label" for="checkbox1">I agree to the terms &
                                        conditions and <a class="ms-2" href="#">Privacy Policy</a></label>
                                    <?php if (has_error('terms')): ?>
                                        <div class="invalid-feedback d-block">
                                            <?= error('terms') ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <button class="btn btn-primary btn-block w-100 mt-3" type="submit">Create
                                    Account</button>
                            </div>


                            <h6 class="text-muted mt-4 or">Or create your account with</h6>
                            <div class="social mt-4">
                                <div class="btn-showcase"><a class="btn btn-light" href="https://www.linkedin.com/login"
                                        target="_blank"><i class="fa-brands fa-linkedin-in"></i></a><a
                                        class="btn btn-light" href="https://twitter.com/login?lang=en"
                                        target="_blank"><i class="fa-brands fa-x-twitter"></i></a><a
                                        class="btn btn-light" href="https://www.facebook.com/" target="_blank"><i
                                            class="fa-brands fa-facebook-f"></i></a><a class="btn btn-light"
                                        href="https://www.google.com/" target="_blank"><i
                                            class="fa-brands fa-google"></i></a></div>
                            </div>
                            <p class="mt-4 mb-0">Already have an account?<a class="ms-2" href="{{ route('login') }}">Sign in</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@endsection