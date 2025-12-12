@extends('backend.layouts.auth.master')

@section('title', 'Login DavinciA')

@section('css')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12 p-0">
            <div class="login-card login-dark">
                <div>
                    <div><a class="logo text-start" href="{{ route('admin.dashboard') }}"><img class="img-fluid for-light"
                                src="{{ asset('assets/images/logo/davinci.png') }}" alt="looginpage" height="50"><img
                                class="img-fluid for-dark" src="{{ asset('assets/images/logo/logo_dark.png') }}"
                                alt="looginpage"></a></div>
                    <div class="login-main">
                        <form class="theme-form" method="POST" action="<?= url('/auth/login') ?>">
                            @csrf
                            <h4>Sign in to account </h4>
                            <p>Enter your email & password to login</p>
                            <div class="form-group">
                                <label class="col-form-label">Email Address</label>
                                <input name="username"
                                    id="username" class="form-control <?= has_error('username') ? 'is-invalid' : '' ?>" type="text" required="" placeholder="Login" value="<?= escape(old('username')) ?>">
                            </div>

                            <?php component('error', ['field' => 'username']) ?>


                            <div class="form-group">
                                <label class="col-form-label">Password</label>
                                <div class="form-input position-relative">
                                    <input type="password"
                                        name="password"
                                        id="password" class="form-control <?= has_error('password') ? 'is-invalid' : '' ?>" type="password" name="password" required=""
                                        placeholder="*********">
                                    <?php component('error', ['field' => 'password']) ?>
                                    <div class="show-hide"><span class="show"> </span></div>
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <div class="form-check">
                                    <input class="checkbox-primary form-check-input" id="checkbox1" type="checkbox">
                                    <label class="text-muted form-check-label" for="checkbox1">Remember password</label>
                                </div><a class="link" href="<?= url('/forgot-password') ?>">Forgot password?</a>
                                <div class="text-end">
                                    <button class="btn btn-primary btn-block w-100 mt-3" type="submit">Sign in</button>
                                </div>
                            </div>
                            <h6 class="text-muted mt-4 or">Or Sign in with</h6>
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
                            <p class="mt-4 mb-0 text-center">Don't have account?<a class="ms-2"
                                    href="{{ route('admin.sign_up') }}">Create Account</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).on('click', '#error', function(e) {
        if ($('.email').val() == '' || $('.pwd').val() == '') {
            swal(
                "Error!", "Sorry, looks like some data are not filled, please try again !", "error"
            )
        }
    });
</script>
@endsection