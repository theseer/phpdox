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

    class Bootstrap {

        public function __construct(ProgressLogger $logger, BootstrapApi $api) {
            $this->logger = $logger;
            $this->api = $api;
        }

        /**
         * Load bootstrap files to register components and builder
         *
         * @param Array $require Array of files to require
         *
         * @return Array Map of BuilderConfig objects ([name => Config])
         */
        public function load(Array $require) {
            $this->loadBootstrap( __DIR__ . '/backends.php');
            $this->loadBootstrap( __DIR__ . '/enrichers.php');
            $this->loadBootstrap( __DIR__ . '/engines.php');

            foreach($require as $file) {
                if (!file_exists($file) || !is_file($file)) {
                    throw new BootstrapException("Require file '$file' not found or not a file", BootstrapException::RequireFailed);
                }
                $this->logger->log("Loading bootstrap file '$file'");
                $this->loadBootstrap($file);
            }

            return $this->api->getEngines();
        }

        public function getBackends() {
            return $this->api->getBackends();
        }

        public function getEngines() {
            return $this->api->getEngines();
        }

        public function getEnrichers() {
            return $this->api->getEnrichers();
        }

        private function loadBootstrap($filename) {
            $phpDox = $this->api;
            require $filename;
        }
    }

    class BootstrapException extends \Exception {
        const RequireFailed = 1;
    }

}