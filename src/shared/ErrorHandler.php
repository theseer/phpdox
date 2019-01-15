<?php declare(strict_types = 1);
namespace TheSeer\phpDox;

class ErrorHandler {
    /**
     * @var Version
     */
    private $version;

    public function __construct(Version $version) {
        $this->version = $version;
    }

    public function __destruct() {
        \restore_exception_handler();
        \restore_error_handler();
    }

    /**
     * Init method
     *
     * Register shutdown, exception and error handler
     */
    public function register(): void {
        \error_reporting(0);
        \ini_set('display_errors', 'off');
        \register_shutdown_function([$this, 'handleShutdown']);
        \set_exception_handler([$this, 'handleException']);
        \set_error_handler([$this, 'handleError'], \E_STRICT | \E_NOTICE | \E_WARNING | \E_RECOVERABLE_ERROR | \E_USER_ERROR);
        \class_exists(\TheSeer\phpDox\ErrorException::class, true);
    }

    /**
     * General System error handler
     *
     * Capture error messages and transform them into an exception
     *
     * @param int    $errno   Error code
     * @param string $errstr  Error message
     * @param string $errfile Filename error occured in
     * @param int    $errline Line of error
     *
     * @throws \ErrorException
     */
    public function handleError($errno, $errstr, $errfile, $errline): void {
        throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    }

    /**
     * System shutdown handler
     *
     * Used to grab fatal errors and handle them gracefully
     */
    public function handleShutdown(): void {
        $error = $this->getLastError();

        if ($error) {
            $exception = new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']);
            $this->handleException($exception);
        }
    }

    /**
     * System Exception Handler
     *
     * @param \Exception|\Throwable $exception The exception to handle
     */
    public function handleException($exception): void {
        \fwrite(\STDERR, "\n\nOups... phpDox encountered a problem and has terminated!\n");
        \fwrite(\STDERR, "\nIt most likely means you've found a bug, so please file a report for this\n");
        \fwrite(\STDERR, "and paste the following details and the stacktrace (if given) along:\n\n");
        \fwrite(\STDERR, 'PHP Version: ' . \PHP_VERSION . ' (' . \PHP_OS . ")\n");
        \fwrite(\STDERR, 'PHPDox Version: ' . $this->version->getVersion() . "\n");
        $this->renderException($exception);
        \fwrite(\STDERR, "\n\n\n");
    }

    public function clearLastError(): void {
        if (\function_exists('error_clear_last')) {
            \error_clear_last();
        } else {
            \set_error_handler(function () {
                return false;
            }, 0);
            @\trigger_error('');
            \restore_error_handler();
        }
    }

    /**
     * @param \Exception|\Throwable $exception
     */
    private function renderException($exception): void {
        if ($exception instanceof ErrorException) {
            \fwrite(\STDERR, \sprintf("ErrorException: %s \n", $exception->getErrorName()));
        } else {
            \fwrite(\STDERR, \sprintf("Exception: %s (Code: %d)\n", \get_class($exception), $exception->getCode()));
        }
        \fwrite(\STDERR, \sprintf("Location: %s (Line %d)\n\n", $exception->getFile(), $exception->getLine()));
        \fwrite(\STDERR, $exception->getMessage() . "\n\n");

        if ($exception instanceof HasFileInfoException) {
            \fwrite(\STDERR, "\nException occured while processing file: " . $exception->getFile() . "\n\n");
        }

        $trace = $exception->getTrace();
        \array_shift($trace);

        if (\count($trace) === 0) {
            \fwrite(\STDERR, 'No stacktrace available');
        }

        foreach ($trace as $pos => $entry) {
            \fwrite(
                \STDERR,
                \sprintf(
                    '#%1$d %2$s(%3$d): %4$s%5$s%6$s()' . "\n",
                    $pos,
                    $entry['file'] ?? 'unknown',
                    $entry['line'] ?? '0',
                    $entry['class'] ?? '',
                    $entry['type'] ?? '',
                    $entry['function'] ?? ''
                )
            );
        }

        $nested = $exception->getPrevious();

        if ($nested !== null) {
            \fwrite(\STDERR, "\n\n");
            $this->renderException($nested);
        }
    }

    /**
     * This method implements a workaround for PHP < 7 where no error_clear_last() exists
     * by considering a last error of type E_USER_NOTICE as "cleared".
     */
    private function getLastError(): array {
        $error = \error_get_last();

        if ($error && $error['type'] === \E_USER_NOTICE) {
            return [];
        }

        return $error;
    }
}
