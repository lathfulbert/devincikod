<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification MFA - Double Authentification</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="<?= asset('assets/css/vendors/bootstrap.css') ?>">
    <!-- Feather Icons -->
    <link rel="stylesheet" type="text/css" href="<?= asset('assets/css/vendors/feather-icon.css') ?>">
    <!-- IcoFont -->
    <link rel="stylesheet" type="text/css" href="<?= asset('assets/css/vendors/icofont.css') ?>">

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

        .mfa-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
            padding: 50px 40px;
            max-width: 500px;
            width: 100%;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h2 {
            color: var(--primary-color);
            font-weight: bold;
            margin: 0;
        }

        .mfa-icon {
            text-align: center;
            margin-bottom: 25px;
        }

        .mfa-icon i,
        .mfa-icon svg {
            width: 64px;
            height: 64px;
            color: var(--primary-color);
            stroke: var(--primary-color);
        }

        .logo svg,
        .logo i {
            display: inline-block;
            vertical-align: middle;
            width: 24px;
            height: 24px;
        }

        label svg,
        label i {
            width: 16px;
            height: 16px;
            vertical-align: middle;
            margin-right: 5px;
        }

        .help-text svg,
        .help-text i {
            width: 14px;
            height: 14px;
            vertical-align: middle;
        }

        .btn svg,
        .btn i {
            width: 16px;
            height: 16px;
            vertical-align: middle;
            margin-right: 5px;
        }

        .alert svg,
        .alert i {
            width: 18px;
            height: 18px;
            vertical-align: middle;
            margin-right: 8px;
        }

        .mfa-main h4 {
            color: #333;
            font-weight: 600;
            margin-bottom: 10px;
            text-align: center;
        }

        .mfa-main p {
            color: #666;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-control,
        .form-select {
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 15px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .code-input {
            font-size: 24px;
            text-align: center;
            letter-spacing: 10px;
            font-weight: 600;
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

        .btn-link {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
            border: none;
            background: none;
            padding: 0;
        }

        .btn-link:hover {
            text-decoration: underline;
            color: var(--secondary-color);
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .method-badge {
            display: inline-block;
            padding: 5px 15px;
            background: rgba(102, 126, 234, 0.1);
            color: var(--primary-color);
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin-left: 10px;
        }

        .help-text {
            font-size: 13px;
            color: #999;
            margin-top: 8px;
        }

        .divider {
            text-align: center;
            position: relative;
            margin: 25px 0;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 1px;
            background: #ddd;
        }

        .divider span {
            position: relative;
            background: white;
            padding: 0 15px;
            color: #999;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="container-fluid p-0">
        <div class="row m-0">
            <div class="col-12 p-0">
                <div class="mfa-card">
                    <div>
                        <div class="logo">
                            <a href="<?= url('/') ?>">
                                <h2>
                                    <i data-feather="shield"></i> SunuFramework
                                </h2>
                            </a>
                        </div>

                        <div class="mfa-icon">
                            <i data-feather="lock"></i>
                        </div>

                        <div class="mfa-main">
                            <?php if (isset($_SESSION['flash_error'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <i data-feather="alert-circle"></i>
                                    <?= $_SESSION['flash_error'] ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                                <?php unset($_SESSION['flash_error']); ?>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['flash_success'])): ?>
                                <div class="alert alert-success alert-dismissible fade show">
                                    <i data-feather="check-circle"></i>
                                    <?= $_SESSION['flash_success'] ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                                <?php unset($_SESSION['flash_success']); ?>
                            <?php endif; ?>

                            <h4>Vérification en deux étapes</h4>
                            <p>Entrez le code de vérification pour sécuriser votre connexion</p>

                            <form method="POST" action="<?= url('/auth/mfa/verify') ?>">
                                <?= csrf_field() ?>

                                <div class="mb-4">
                                    <label for="method" class="form-label">
                                        <i data-feather="shield"></i> Méthode de vérification
                                    </label>
                                    <select class="form-select" id="method" name="method" required>
                                        <?php foreach ($methods as $method): ?>
                                            <option value="<?= $method['type'] ?>">
                                                <?php if ($method['type'] === 'totp'): ?>
                                                    📱 Application d'authentification (TOTP)
                                                <?php elseif ($method['type'] === 'sms'): ?>
                                                    💬 SMS - <?= $method['value'] ?? 'Téléphone' ?>
                                                <?php elseif ($method['type'] === 'email'): ?>
                                                    📧 Email - <?= $method['value'] ?? 'Email' ?>
                                                <?php else: ?>
                                                    <?= ucfirst($method['type']) ?>
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="help-text">
                                        <i data-feather="info"></i>
                                        Choisissez votre méthode de vérification préférée
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="code" class="form-label">
                                        <i data-feather="key"></i> Code de vérification
                                    </label>
                                    <input type="text"
                                           class="form-control code-input"
                                           id="code"
                                           name="code"
                                           required
                                           autofocus
                                           maxlength="6"
                                           pattern="[0-9]{6}"
                                           placeholder="000000"
                                           autocomplete="one-time-code">
                                    <div class="help-text">
                                        <i data-feather="clock"></i>
                                        Le code expire dans quelques minutes
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i data-feather="check"></i> Vérifier le code
                                </button>
                            </form>

                            <div class="divider">
                                <span>Besoin d'aide ?</span>
                            </div>

                            <div class="text-center">
                                <button class="btn btn-link" onclick="sendOtp(event)">
                                    <i data-feather="send"></i> Renvoyer un nouveau code
                                </button>
                            </div>

                            <div class="text-center mt-3">
                                <a class="btn btn-link" href="<?= url('/auth/logout') ?>">
                                    <i data-feather="arrow-left"></i> Annuler et se déconnecter
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="<?= asset('assets/js/jquery.min.js') ?>"></script>
    <!-- Bootstrap JS -->
    <script src="<?= asset('assets/js/bootstrap/bootstrap.bundle.min.js') ?>"></script>
    <!-- Feather Icons -->
    <script src="<?= asset('assets/js/icons/feather-icon/feather.min.js') ?>"></script>

    <script>
        // Auto-format code input (remove non-digits)
        const codeInput = document.getElementById('code');
        codeInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Send OTP function
        function sendOtp(e) {
            e.preventDefault();

            const method = document.getElementById('method').value;
            const button = e.target;

            // Disable button and show loading
            button.disabled = true;
            const originalText = button.innerHTML;
            button.innerHTML = '<i data-feather="loader"></i> Envoi en cours...';

            // Replace the loader icon
            if (typeof feather !== 'undefined') {
                feather.replace();
            }

            // Get CSRF token from the main form
            const csrfInput = document.querySelector('input[name="_csrf_token"]');
            const csrfToken = csrfInput ? csrfInput.value : '';

            // Prepare form data
            const formData = new URLSearchParams();
            formData.append('method', method);
            formData.append('_csrf_token', csrfToken);

            fetch('<?= url('/auth/mfa/send-otp') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData.toString()
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    // Try to get error details
                    return response.text().then(text => {
                        console.error('Error response:', text);
                        throw new Error(`Server error: ${response.status} - ${text.substring(0, 100)}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                // Re-enable button
                button.disabled = false;
                button.innerHTML = originalText;

                // Log response for debugging
                console.log('OTP Response:', data);

                // Show message
                if (data.success) {
                    showAlert('success', data.message || 'Code envoyé avec succès !');
                } else {
                    showAlert('danger', data.message || 'Erreur lors de l\'envoi du code');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                button.disabled = false;
                button.innerHTML = originalText;
                showAlert('danger', 'Erreur de connexion. Veuillez réessayer.');
            });
        }

        // Show alert helper
        function showAlert(type, message) {
            const icon = type === 'success' ? 'check-circle' : 'alert-circle';
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show">
                    <i data-feather="${icon}"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;

            const mfaMain = document.querySelector('.mfa-main');
            mfaMain.insertAdjacentHTML('afterbegin', alertHtml);

            // Replace feather icons in the new alert
            if (typeof feather !== 'undefined') {
                feather.replace();
            }

            // Auto-remove after 5 seconds
            setTimeout(() => {
                const alert = mfaMain.querySelector('.alert');
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        }

        // Initialize Feather icons on page load
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    </script>
</body>

</html>