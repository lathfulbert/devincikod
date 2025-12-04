<!DOCTYPE html>
<html lang="fr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($config->message ?? 'Site en maintenance') ?>">
    <link rel="icon" href="<?= asset('assets/images/favicon.png') ?>" type="image/x-icon">
    <title><?= htmlspecialchars($config->title ?? 'Site en Maintenance') ?></title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Rubik:400,500,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="<?= asset('assets/css/vendors/bootstrap.css') ?>">

    <!-- Feather Icons -->
    <link rel="stylesheet" type="text/css" href="<?= asset('assets/css/vendors/feather-icon.css') ?>">

    <!-- Custom Styles -->
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Rubik', sans-serif;
            overflow: hidden;
        }

        .maintenance-wrapper {
            position: relative;
            width: 100%;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            <?php if ($config->background_image): ?>
                background-image: url('<?= asset($config->background_image) ?>');
                background-size: cover;
                background-position: center;
            <?php else: ?>
                background: linear-gradient(135deg, <?= $config->background_color ?? '#4466f2' ?> 0%, <?= adjustBrightness($config->background_color ?? '#4466f2', -20) ?> 100%);
            <?php endif; ?>
        }

        .maintenance-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }

        .maintenance-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
            padding: 40px;
            max-width: 800px;
        }

        .maintenance-logo {
            max-width: 200px;
            margin-bottom: 30px;
        }

        .maintenance-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .maintenance-message {
            font-size: 1.2rem;
            margin-bottom: 40px;
            opacity: 0.95;
            line-height: 1.6;
        }

        .countdown {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 40px;
        }

        .countdown-item {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 20px 30px;
            min-width: 100px;
        }

        .countdown-value {
            display: block;
            font-size: 3rem;
            font-weight: 700;
            line-height: 1;
        }

        .countdown-label {
            display: block;
            font-size: 0.9rem;
            text-transform: uppercase;
            margin-top: 10px;
            opacity: 0.8;
        }

        .maintenance-icon {
            font-size: 5rem;
            margin-bottom: 20px;
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .maintenance-title {
                font-size: 2rem;
            }

            .maintenance-message {
                font-size: 1rem;
            }

            .countdown {
                flex-wrap: wrap;
                gap: 10px;
            }

            .countdown-item {
                padding: 15px 20px;
                min-width: 80px;
            }

            .countdown-value {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
    <div class="maintenance-wrapper">
        <?php if ($config->background_image): ?>
            <div class="maintenance-overlay"></div>
        <?php endif; ?>

        <div class="maintenance-content">
            <?php
            // Try to load logo
            $logoPath = asset('assets/images/logo/logo.png');
            if (file_exists(__DIR__ . '/../../public/assets/images/logo/logo-light.png')) {
                $logoPath = asset('assets/images/logo/logo-light.png');
            }
            ?>
            <img src="<?= $logoPath ?>" alt="Logo" class="maintenance-logo">

            <div class="maintenance-icon">
                <i data-feather="tool"></i>
            </div>

            <h1 class="maintenance-title">
                <?= htmlspecialchars($config->title ?? 'Site en Maintenance') ?>
            </h1>

            <p class="maintenance-message">
                <?= nl2br(htmlspecialchars($config->message ?? 'Nous effectuons une maintenance technique. Nous serons de retour très bientôt !')) ?>
            </p>

            <?php if ($config->show_countdown && $config->end_time): ?>
                <?php
                $endTime = strtotime($config->end_time);
                $now = time();
                if ($endTime > $now):
                    $remaining = $endTime - $now;
                    $days = floor($remaining / 86400);
                    $hours = floor(($remaining % 86400) / 3600);
                    $minutes = floor(($remaining % 3600) / 60);
                    $seconds = $remaining % 60;
                ?>
                    <div class="countdown" id="countdown" data-end-time="<?= $endTime ?>">
                        <div class="countdown-item">
                            <span class="countdown-value" id="days"><?= $days ?></span>
                            <span class="countdown-label">Jours</span>
                        </div>
                        <div class="countdown-item">
                            <span class="countdown-value" id="hours"><?= $hours ?></span>
                            <span class="countdown-label">Heures</span>
                        </div>
                        <div class="countdown-item">
                            <span class="countdown-value" id="minutes"><?= $minutes ?></span>
                            <span class="countdown-label">Minutes</span>
                        </div>
                        <div class="countdown-item">
                            <span class="countdown-value" id="seconds"><?= $seconds ?></span>
                            <span class="countdown-label">Secondes</span>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?= asset('assets/js/icons/feather-icon/feather.min.js') ?>"></script>
    <script>
        // Initialize Feather Icons
        feather.replace();

        <?php if ($config->show_countdown && $config->end_time): ?>
        // Countdown Timer
        function updateCountdown() {
            const endTime = <?= $endTime ?>;
            const now = Math.floor(Date.now() / 1000);
            let remaining = endTime - now;

            if (remaining <= 0) {
                // Maintenance period ended, reload page
                location.reload();
                return;
            }

            const days = Math.floor(remaining / 86400);
            const hours = Math.floor((remaining % 86400) / 3600);
            const minutes = Math.floor((remaining % 3600) / 60);
            const seconds = remaining % 60;

            document.getElementById('days').textContent = days;
            document.getElementById('hours').textContent = String(hours).padStart(2, '0');
            document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
            document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
        }

        // Update every second
        setInterval(updateCountdown, 1000);
        <?php endif; ?>
    </script>
</body>
</html>

<?php
// Helper function to adjust color brightness
function adjustBrightness($hex, $percent) {
    $hex = str_replace('#', '', $hex);

    if (strlen($hex) == 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }

    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    $r = (int) max(0, min(255, $r + ($r * $percent / 100)));
    $g = (int) max(0, min(255, $g + ($g * $percent / 100)));
    $b = (int) max(0, min(255, $b + ($b * $percent / 100)));

    return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT)
              . str_pad(dechex($g), 2, '0', STR_PAD_LEFT)
              . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
}
?>
