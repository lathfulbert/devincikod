<?php

/**
 * Flash Messages Display
 * Displays flash messages using SweetAlert2 toast notifications.
 */

// Load SweetAlert2 if not already loaded
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
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