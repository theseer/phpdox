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

    use TheSeer\fDOM\fDOMElement;

    class ProjectConfig {

        /**
         * @var Version
         */
        private $version;

        /**
         * @var fDOMElement;
         */
        private $ctx;

        /**
         * @var FileInfo
         */
        private $homeDir;

        /**
         * Constructor for global config
         *
         * @param fDOMElement $ctx   Reference to <project> node
         */
        public function __construct(Version $version, FileInfo $homeDir, fDOMElement $ctx) {
            $this->version = $version;
            $this->homeDir = $homeDir;
            $this->ctx = $ctx;
        }

        /**
         * @return Version
         */
        public function getVersion() {
            return $this->version;
        }

        /**
         * @return Fileinfo
         */
        public function getHomeDirectory() {
            return $this->homeDir;
        }

        /**
         * @return FileInfo
         */
        public function getWorkDirectory() {
            return new FileInfo($this->ctx->getAttribute('workdir', 'xml'));
        }

        /**
         * @return FileInfo
         */
        public function getSourceDirectory() {
            return new FileInfo($this->ctx->getAttribute('source', 'src'));
        }

        /**
         * @return bool
         */
        public function isPublicOnlyMode() {
            return $this->ctx->getAttribute('publiconly', 'false') === 'true';
        }

        /**
         * @return CollectorConfig
         * @throws ConfigException
         */
        public function getCollectorConfig() {
            $colNode = $this->ctx->queryOne('cfg:collector');
            if (!$colNode) {
                throw new ConfigException("Project does not have a collector section", ConfigException::NoCollectorSection);
            }
            return new CollectorConfig($this, $colNode);
        }

        /**
         * @return GeneratorConfig
         * @throws ConfigException
         */
        public function getGeneratorConfig() {
            $genNode = $this->ctx->queryOne('cfg:generator');
            if (!$genNode) {
                throw new ConfigException("Project does not have a generator section", ConfigException::NoGeneratorSection);
            }
            return new GeneratorConfig($this, $genNode);
        }

    }

}
