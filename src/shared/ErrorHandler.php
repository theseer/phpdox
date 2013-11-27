<?php
/**
 * Copyright (c) 2010-2013 Arne Blankerts <arne@blankerts.de>
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

        protected $debugMode = false;

        /**
         * Init method
         *
         * Register shutdown, exception and error handler
         *
         * @return void
         */
        public function register() {
            error_reporting(-1);
            ini_set('display_errors', FALSE);
            register_shutdown_function(array($this, "handleShutdown"));
            set_exception_handler(array($this, 'handleException'));
            set_error_handler(array($this, 'handleError'), E_STRICT|E_NOTICE|E_WARNING|E_RECOVERABLE_ERROR|E_USER_ERROR);
        }

        public function setDebug($mode) {
            $this->debugMode = ($mode === true);
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
            if (ini_get('error_reporting')==0 && !$this->debugMode) {
                return true;
            }
            throw new \ErrorException($errstr, -1, $errno, $errfile, $errline);
        }


        /**
         * System shutdown handler
         *
         * Used to grab fatal errors and handle them gracefully
         *
         * @return void
         */
        public function handleShutdown() {
            $error = error_get_last();
            if ($error && in_array($error['type'], array(E_ERROR, E_PARSE, E_RECOVERABLE_ERROR))) {
                $exception = new \ErrorException($error['message'], -1, $error['type'], $error['file'], $error['line']);
                $this->handleException($exception);
            }
        }

        /**
         * System Exception Handler
         *
         * @param \Exception $exception The exception to handle
         *
         * @return void
         */
        public function handleException(\Exception $exception) {
            fwrite(STDERR, "\n\nOups... phpDox encountered a problem and has terminated!\n");
            fwrite(STDERR, "\nIt most likely means you've found a bug, so please file a report for this\n");
            fwrite(STDERR, "and paste the following details and the stacktrace (if given) along:\n\n");
            fwrite(STDERR, "Version: " . Version::getVersion() . "\n");
            $this->renderException($exception);
            fwrite(STDERR, "\n\n\n");
            exit(1);
        }

        protected function renderException(\Exception $exception) {
            fwrite(STDERR, sprintf("Exception: %s\n", get_class($exception)));
            fwrite(STDERR, sprintf("Location: %s (Line %d)\n\n", $exception->getFile(), $exception->getLine()));
            fwrite(STDERR, $exception->getMessage() . "\n\n");

            if ($exception instanceof HasFileInfoException) {
                fwrite(STDERR, "\nException occured while processing file: " .  $exception->getFile()."\n\n");
            }

            $trace = $exception->getTrace();
            array_shift($trace);
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
    }

}