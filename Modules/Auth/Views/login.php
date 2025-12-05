<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }

        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
            padding: 50px 40px;
            max-width: 450px;
            width: 100%;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo img {
            max-width: 150px;
            height: auto;
        }

        .login-main h4 {
            color: #333;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .login-main p {
            color: #666;
            margin-bottom: 30px;
        }

        .form-control {
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 15px;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .form-input.position-relative {
            position: relative;
        }

        .show-hide {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }

        .show-hide:hover {
            color: var(--primary-color);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            padding: 12px;
            font-size: 16px;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .link {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
        }

        .link:hover {
            text-decoration: underline;
        }

        .or {
            text-align: center;
            position: relative;
            margin: 25px 0;
        }

        .or::before,
        .or::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 40%;
            height: 1px;
            background: #ddd;
        }

        .or::before {
            left: 0;
        }

        .or::after {
            right: 0;
        }

        .social .btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 5px;
            transition: all 0.3s ease;
        }

        .social .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container-fluid p-0">
        <div class="row m-0">
            <div class="col-12 p-0">
                <div class="login-card">
                    <div>
                        <div class="logo">
                            <a href="<?= url('/') ?>">
                                <!-- Remplacez par votre logo -->
                                <h2 style="color: var(--primary-color); font-weight: bold;">
                                    🔐 SunuFramework
                                </h2>
                            </a>
                        </div>

                        <div class="login-main">
                            <?php if (isset($_SESSION['flash_error'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <?= $_SESSION['flash_error'] ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                                <?php unset($_SESSION['flash_error']); ?>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['flash_success'])): ?>
                                <div class="alert alert-success alert-dismissible fade show">
                                    <?= $_SESSION['flash_success'] ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                                <?php unset($_SESSION['flash_success']); ?>
                            <?php endif; ?>

                            <form class="theme-form" method="POST" action="<?= url('/auth/login') ?>">
                                <?= csrf_field() ?>

                                <h4>Connexion à votre compte</h4>
                                <p>Entrez votre email ou username et mot de passe</p>

                                <div class="form-group mb-3">
                                    <label class="col-form-label">Email ou Nom d'utilisateur</label>
                                    <input class="form-control" type="text" name="identifier" required
                                        placeholder="email@exemple.com ou username" autofocus>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="col-form-label">Mot de passe</label>
                                    <div class="form-input position-relative">
                                        <input class="form-control" type="password" name="password"
                                            id="password-input" required placeholder="*********">
                                        <div class="show-hide" onclick="togglePassword()">
                                            <span class="show" id="toggle-icon">
                                                <i class="fa fa-eye"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <div class="form-check">
                                        <input class="checkbox-primary form-check-input" id="checkbox1" type="checkbox">
                                        <label class="text-muted form-check-label" for="checkbox1">
                                            Se souvenir de moi
                                        </label>
                                    </div>
                                    <a class="link" href="<?= url('/auth/forgot-password') ?>">
                                        Mot de passe oublié ?
                                    </a>

                                    <div class="text-end">
                                        <button class="btn btn-primary btn-block w-100 mt-3" type="submit">
                                            Se connecter
                                        </button>
                                    </div>
                                </div>

                                <h6 class="text-muted mt-4 or">Ou se connecter avec</h6>

                                <div class="social mt-4">
                                    <div class="btn-showcase text-center">
                                        <a class="btn btn-light" href="#" title="LinkedIn">
                                            <i class="fa-brands fa-linkedin-in"></i>
                                        </a>
                                        <a class="btn btn-light" href="#" title="Twitter">
                                            <i class="fa-brands fa-x-twitter"></i>
                                        </a>
                                        <a class="btn btn-light" href="#" title="Facebook">
                                            <i class="fa-brands fa-facebook-f"></i>
                                        </a>
                                        <a class="btn btn-light" href="#" title="Google">
                                            <i class="fa-brands fa-google"></i>
                                        </a>
                                    </div>
                                </div>

                                <p class="mt-4 mb-0 text-center">
                                    Pas encore de compte ?
                                    <a class="ms-2 link" href="<?= url('/auth/register') ?>">
                                        Créer un compte
                                    </a>
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password-input');
            const toggleIcon = document.getElementById('toggle-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.innerHTML = '<i class="fa fa-eye-slash"></i>';
            } else {
                passwordInput.type = 'password';
                toggleIcon.innerHTML = '<i class="fa fa-eye"></i>';
            }
        }
    </script>
</body>

</html>