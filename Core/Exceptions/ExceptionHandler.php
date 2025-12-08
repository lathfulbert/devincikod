<?php

namespace App\Core\Exceptions;

use Throwable;
use App\Core\Application;

class ExceptionHandler
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Register the exception handling callbacks.
     */
    public function register(): void
    {
        error_reporting(-1);

        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * Convert PHP errors to ErrorException.
     */
    public function handleError($level, $message, $file = '', $line = 0): void
    {
        if (error_reporting() & $level) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Handle an uncaught exception.
     */
    public function handleException(Throwable $e): void
    {
        try {
            $this->report($e);
        } catch (Throwable $reportException) {
            // If reporting fails, just silence it to avoid infinite loops
        }

        $this->render($e);
    }

    /**
     * Handle the PHP shutdown event.
     */
    public function handleShutdown(): void
    {
        if (!is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
            $this->handleException(
                new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line'])
            );
        }
    }

    /**
     * Report or log an exception.
     */
    public function report(Throwable $e): void
    {
        // Don't report 404s if you don't want to pollute logs
        // if ($e instanceof NotFoundException) return;

        logger()->error($e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'url' => $_SERVER['REQUEST_URI'] ?? 'CLI',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
        ]);
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render(Throwable $e): void
    {
        // Simple error page for now
        if (php_sapi_name() === 'cli') {
            echo "\n\033[31mError: " . $e->getMessage() . "\033[0m\n";
            echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
            return;
        }

        http_response_code(500);

        // Use environment helpers to determine error display
        $showDetails = isDebugMode() || config('app.show_error_details', false);

        if ($showDetails) {
            $this->renderDebug($e);
        } else {
            $this->renderGeneric();
        }
    }

    protected function renderDebug(Throwable $e): void
    {
        // Format error details for debug mode
        $error = get_class($e) . ": " . $e->getMessage() . "\n\n";
        $error .= "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
        $error .= "Stack trace:\n" . $e->getTraceAsString();

        // Use the error page with debug information
        \App\Core\Routing\Router::handleError(500, 'Erreur Serveur', $error);
    }

    protected function renderGeneric(): void
    {
        // Use the generic 500 error page
        \App\Core\Routing\Router::handleError(500, 'Erreur Serveur');
    }

    protected function isFatal($type): bool
    {
        return in_array($type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE]);
    }
}
