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

    class CollectorConfig {

        protected $ctx;
        protected $project;

        public function __construct(ProjectConfig $project, fDOMElement $ctx) {
            $this->project = $project;
            $this->ctx = $ctx;
        }

        public function getProjectConfig() {
            return $this->project;
        }

        public function getBackend() {
            if ($this->ctx->hasAttribute('backend')) {
                return $this->ctx->getAttribute('backend', 'parser');
            }
            return 'parser';
        }

        /**
         * @return FileInfo
         */
        public function getWorkDirectory() {
            return $this->project->getWorkDirectory();
        }

        /**
         * @return FileInfo
         */
        public function getSourceDirectory() {
            return $this->project->getSourceDirectory();
        }

        /**
         * @return string
         */
        public function getFileEncoding() {
            return $this->ctx->getAttribute('encoding', 'auto');
        }

        public function isPublicOnlyMode() {
            if ($this->ctx->hasAttribute('publiconly')) {
                return $this->ctx->getAttribute('publiconly', 'false') === 'true';
            }
            return $this->project->isPublicOnlyMode();
        }

        public function getIncludeMasks() {
            return $this->getMasks('include') ?: '*.php';
        }

        public function getExcludeMasks() {
            return $this->getMasks('exclude');
        }

        public function doResolveInheritance() {
            $inNode = $this->ctx->queryOne('cfg:inheritance');
            if (!$inNode) {
                return true;
            }
            return $inNode->getAttribute('resolve', 'true')=='true';
        }

        public function getInheritanceConfig() {
            return new InheritanceConfig($this, $this->ctx->queryOne('cfg:inheritance'));
        }

        protected function getMasks($nodename) {
            $list = array();
            foreach($this->ctx->query('cfg:'.$nodename) as $node) {
                $list[] = $node->getAttribute('mask');
            }
            return $list;
        }
    }

}
