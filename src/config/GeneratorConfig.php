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

    class GeneratorConfig {

        /**
         * @var array
         */
        private $builds;

        /**
         * @var array
         */
        private $enrichers;

        /**
         * @var fDOMElement
         */
        private $ctx;

        /**
         * @var ProjectConfig
         */
        private $project;

        public function __construct(ProjectConfig $project, fDOMElement $ctx) {
            $this->project = $project;
            $this->ctx = $ctx;
        }

        public function getProjectConfig() {
            return $this->project;
        }

        public function getActiveBuilds() {
            if (!is_array($this->builds)) {
                $this->builds = array();
                foreach($this->ctx->query('cfg:build[@engine and (not(@enabled) or @enabled="true")]') as $ctx) {
                    $this->builds[] = new BuildConfig($this, $ctx);
                }
            }
            return $this->builds;
        }

        public function getRequiredEngines() {
            $engines = array();
            foreach($this->getActiveBuilds() as $build) {
                $engines[] = $build->getEngine();
            }
            return array_unique($engines);
        }

        public function getRequiredEnrichers() {
            $enrichers = array();
            foreach($this->getActiveEnrichSources() as $source) {
                $enrichers[] = $source->getType();
            }
            return array_unique($enrichers);
        }

        public function getActiveEnrichSources() {
            if (!is_array($this->enrichers)) {
                $this->enrichers = array();
                foreach($this->ctx->query('cfg:enrich/cfg:source[@type and (not(@enabled) or @enabled="true")]') as $ctx) {
                    $this->enrichers[$ctx->getAttribute('type')] = new EnrichConfig($this, $ctx);
                }
                if (!isset($this->enrichers['build'])) {
                    $ctx = $this->ctx->ownerDocument->createElementNS('http://xml.phpdox.net/config', 'source');
                    $ctx->setAttribute('type', 'build');
                    $this->enrichers['build'] = new EnrichConfig($this, $ctx);
                }
            }
            return $this->enrichers;
        }
    }

}
