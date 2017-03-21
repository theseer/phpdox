<?php
/**
 * Copyright (c) 2010-2017 Arne Blankerts <arne@blankerts.de>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 *   * Redistributions of source code must retain the above copyright notice,
 *     this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright notice,
 *     this list of conditions and the following disclaimer in the documentation
 *     and/or other materials provided with the distribution.
 *
 *   * Neither the name of Arne Blankerts nor the names of contributors
 *     may be used to endorse or promote products derived from this software
 *     without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT  * NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER ORCONTRIBUTORS
 * BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    phpDox
 * @author     Arne Blankerts <arne@blankerts.de>
 * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
 * @license    BSD License
 *
 */
namespace TheSeer\phpDox {

    class ErrorHandler {

        /**
         * @var Version
         */
        private $version;

        /**
         * ErrorHandler constructor.
         *
         * @param Version $version
         */
        public function __construct(Version $version) {
            $this->version = $version;
        }

        /**
         * Init method
         *
         * Register shutdown, exception and error handler
         *
         * @return void
         */
        public function register() {
            error_reporting(0);
            ini_set('display_errors', FALSE);
            register_shutdown_function(array($this, "handleShutdown"));
            set_exception_handler(array($this, 'handleException'));
            set_error_handler(array($this, 'handleError'), E_STRICT|E_NOTICE|E_WARNING|E_RECOVERABLE_ERROR|E_USER_ERROR);
            class_exists('\TheSeer\phpDox\ErrorException', true);
        }

        /**
         * Destructor
         *
         * @return void
         */
        public function __destruct() {
            restore_exception_handler();
            restore_error_handler();
        }

        /**
         * General System error handler
         *
         * Capture error messages and transform them into an exception
         *
         * @param integer $errno   Error code
         * @param string  $errstr  Error message
         * @param string  $errfile Filename error occured in
         * @param integer $errline Line of error
         *
         * @throws \ErrorException
         */
        public function handleError($errno, $errstr, $errfile, $errline) {
            throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
        }

        /**
         * System shutdown handler
         *
         * Used to grab fatal errors and handle them gracefully
         *
         * @return void
         */
        public function handleShutdown() {
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
         *
         * @return void
         */
        public function handleException($exception) {
            fwrite(STDERR, "\n\nOups... phpDox encountered a problem and has terminated!\n");
            fwrite(STDERR, "\nIt most likely means you've found a bug, so please file a report for this\n");
            fwrite(STDERR, "and paste the following details and the stacktrace (if given) along:\n\n");
            fwrite(STDERR, "PHP Version: " . PHP_VERSION . " (" . PHP_OS . ")\n");
            fwrite(STDERR, "PHPDox Version: " . $this->version->getVersion() . "\n");
            $this->renderException($exception);
            fwrite(STDERR, "\n\n\n");
        }

        /**
         * @param \Exception|\Throwable $exception
         */
        private function renderException($exception) {
            if ($exception instanceof ErrorException) {
                fwrite(STDERR, sprintf("ErrorException: %s \n", $exception->getErrorName()));
            } else {
                fwrite(STDERR, sprintf("Exception: %s (Code: %d)\n", get_class($exception), $exception->getCode()));
            }
            fwrite(STDERR, sprintf("Location: %s (Line %d)\n\n", $exception->getFile(), $exception->getLine()));
            fwrite(STDERR, $exception->getMessage() . "\n\n");

            if ($exception instanceof HasFileInfoException) {
                fwrite(STDERR, "\nException occured while processing file: " .  $exception->getFile()."\n\n");
            }

            $trace = $exception->getTrace();
            array_shift($trace);
            if (count($trace) == 0) {
                fwrite(STDERR, 'No stacktrace available');
            }
            foreach($trace as $pos => $entry) {
                fwrite(STDERR,
                    sprintf('#%1$d %2$s(%3$d): %4$s%5$s%6$s()'."\n",
                        $pos,
                        isset($entry['file']) ? $entry['file'] : 'unknown',
                        isset ($entry['line']) ? $entry['line'] : '0',
                        isset($entry['class']) ? $entry['class'] : '',
                        isset($entry['type']) ? $entry['type'] : '',
                        isset($entry['function']) ? $entry['function'] : ''
                    )
                );
            }

            $nested = $exception->getPrevious();
            if ($nested !== NULL) {
                fwrite(STDERR, "\n\n");
                $this->renderException($nested);
            }

        }

        public function clearLastError() {
            if (function_exists('error_clear_last')) {
                error_clear_last();
            } else {
                set_error_handler(function () { return false; }, 0);
                @trigger_error('');
                restore_error_handler();
            }
        }

        /**
         * This method implements a workaround for PHP < 7 where no error_clear_last() exists
         * by considering a last error of type E_USER_NOTICE as "cleared".
         *
         * @return array
         */
        private function getLastError() {
            $error = error_get_last();
            if ($error && $error['type'] == E_USER_NOTICE) {
                return [];
            }
            return $error;
        }
    }

}
