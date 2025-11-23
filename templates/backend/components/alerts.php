<?php

/**
 * Component: Alerts
 * Affiche des messages flash stylisés via SweetAlert2 toast notifications.
 */
if (isset($_SESSION['flash'])):
    foreach ($_SESSION['flash'] as $type => $messages):
        if (!is_array($messages)) {
            $messages = [$messages];
        }
        // Mapping type to SweetAlert2 icon
        $iconMap = [
            'success' => 'success',
            'danger'  => 'error',
            'warning' => 'warning',
            'info'    => 'info',
        ];
        $icon = $iconMap[$type] ?? 'info';
        foreach ($messages as $message):
            $jsMessage = json_encode($message);
?>
            <script>
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: '<?php echo $icon; ?>',
                    title: <?php echo $jsMessage; ?>,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
            </script>
<?php
        endforeach;
    endforeach;
    unset($_SESSION['flash']);
endif;
?>