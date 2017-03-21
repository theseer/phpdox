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

    /**
     * Shell output based logger
     */
    class ShellProgressLogger implements ProgressLogger {

        /**
         * @var array
         */
        private $stateChars;

        /**
         * @var int
         */
        private $totalCount = 0;
        /**
         * @var array
         */
        private $stateCount = array(
            'processed' => 0,
            'cached' => 0,
            'failed' => 0
        );

        /**
         * @param string $processed
         * @param string $cached
         * @param string $failed
         */
        public function __construct($processed = '.', $cached = 'c', $failed = 'f') {
            $this->stateChars = array(
                'processed' => $processed,
                'cached' => $cached,
                'failed' => $failed
            );
        }

        /**
         * @param $state
         * @throws ProgressLoggerException
         */
        public function progress($state) {
            if (!isset($this->stateChars[$state])) {
                throw new ProgressLoggerException("Unkown progress state '$state'", ProgressLoggerException::UnknownState);
            }
            $this->stateCount[$state]++;
            $this->totalCount++;

            echo $this->stateChars[$state];
            if ($this->totalCount % 50 == 0) {
                echo "\t[". $this->totalCount . "]\n";
            }
        }

        /**
         *
         */
        public function reset() {
            $this->totalCount = 0;
            $this->stateCount = array(
                'processed' => 0,
                'cached' => 0,
                'failed' => 0
            );
        }

        /**
         *
         */
        public function completed() {
            $pad = (ceil($this->totalCount / 50) * 50) - $this->totalCount;
            if ($pad !=0) {
                echo str_pad('', $pad, ' ') . "\t[". $this->totalCount . "]\n";
            }
            echo "\n";
        }

        /**
         * @param $msg
         */
        public function log($msg) {
            if (func_num_args()>1) {
                $msg = vsprintf($msg, array_slice(func_get_args(), 1));
            }
            echo "[" . date('d.m.Y - H:i:s') . '] ' . $msg . "\n";
        }

        /**
         *
         */
        public function buildSummary() {
            echo "\n\n";
            echo \PHP_Timer::resourceUsage();
            echo "\n\n";
        }

    }

}
