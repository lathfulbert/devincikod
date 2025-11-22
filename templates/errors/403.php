<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Forbidden</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8f9fa;
            color: #343a40;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .error-container {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 90%;
        }

        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: #dc3545;
            margin: 0;
            line-height: 1;
        }

        .error-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #495057;
        }

        .error-message {
            color: #6c757d;
            margin-bottom: 2rem;
        }

        .btn {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            text-decoration: none;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            cursor: pointer;
        }

        .btn-primary {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0069d9;
            border-color: #0062cc;
        }

        .btn-secondary {
            color: #fff;
            background-color: #6c757d;
            border-color: #6c757d;
            margin-left: 0.5rem;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="error-code">403</div>
        <h1 class="error-title">Accès Interdit</h1>
        <p class="error-message">
            <?php echo isset($_SESSION['_error']) ? htmlspecialchars($_SESSION['_error']) : 'Vous n\'avez pas la permission d\'accéder à cette page.'; ?>
            <?php unset($_SESSION['_error']); ?>
        </p>
        <div>
            <a href="<?php echo url('/'); ?>" class="btn btn-primary">Accueil</a>
            <?php if (function_exists('auth') && auth()->check()): ?>
                <a href="<?php echo url('/admin'); ?>" class="btn btn-secondary">Dashboard</a>
            <?php else: ?>
                <a href="<?php echo url('/login'); ?>" class="btn btn-secondary">Connexion</a>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>