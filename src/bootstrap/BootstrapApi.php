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
 * @author     Arne Blankerts <arne@blankerts.de>
 * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
 * @license    BSD License
 *
 */
namespace TheSeer\phpDox {

    use \TheSeer\phpDox\Collector\Backend\Factory as BackendFactory;
    use \TheSeer\phpDox\DocBlock\Factory as DocBlockFactory;
    use \TheSeer\phpDox\Generator\Engine\Factory as EngineFactory;
    use \TheSeer\phpDox\Generator\Enricher\Factory as EnricherFactory;

    /**
     * Bootstrapping API for registering backends, generator engines and parsers
     *
     * This class provides the API for use within the bootstrap process to register
     * collecting backends, additional parsers for annotations, generator engines for
     * additional output formats as well as enrichment plugins
     *
     * @author     Arne Blankerts <arne@blankerts.de>
     * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
     * @license    BSD License
     *
     */
    class BootstrapApi {

        /**
         * Reference to the BackendFactory instance
         *
         * @var BackendFactory
         */
        private $backendFactory;

        /**
         * Reference to the EngineFactory instance
         *
         * @var EngineFactory
         */
        private $engineFactory;

        /**
         * Reference to the DocblockParserFactory instance
         *
         * @var DocBlockFactory
         */
        private $parserFactory;

        /**
         * @var EnricherFactory
         */
        private $enricherFactory;

        /**
         * Array of registered engines
         *
         * @var array
         */
        private $engines = array();

        /**
         * Array of registered enrichers
         *
         * @var array
         */
        private $enrichers = array();
        /**
         * Array of registered backends
         *
         * @var array
         */
        private $backends = array();

        /**
         * Constructor
         *
         * @param FactoryInterface $factory
         */
        public function __construct(BackendFactory $bf, DocBlockFactory $df, EnricherFactory $erf, EngineFactory $enf, ProgressLogger $logger) {
            $this->backendFactory = $bf;
            $this->engineFactory = $enf;
            $this->parserFactory = $df;
            $this->enricherFactory = $erf;
            $this->logger = $logger;
        }

        /**
         * Get list of all registered generator engines
         *
         * @return array
         */
        public function getEngines() {
            return $this->engines;
        }

        /**
         * Get list of all registered enrichers
         *
         * @return array
         */
        public function getEnrichers() {
            return $this->enrichers;
        }

        /**
         * Get list of all registered collector backends
         *
         * @return array
         */
        public function getBackends() {
            return $this->backends;
        }

        /**
         * Register a new backend
         *
         * @param string $name        Name of the collector backend
         * @param string $description A describing text
         *
         * @return BackendBootstrapApi
         */
        public function registerBackend($name, $description = 'no description set') {
            $this->logger->log("Registered collector backend '$name'");
            $this->backends[$name] = $description;
            return new BackendBootstrapApi($name, $this->backendFactory);
        }

        /**
         * Register a new generator enginge
         *
         * @param string $name         Name of the generator engine
         * @param string $description  A describing text
         *
         * @return EngineBootstrapApi
         */
        public function registerEngine($name, $description) {
            $this->logger->log("Registered output engine '$name'");
            $this->engines[$name] = $description;
            return new EngineBootstrapApi($name, $this->engineFactory);
        }

        /**
         * @param $annotation
         * @return ParserBootstrapApi
         */
        public function registerParser($annotation) {
            $this->logger->log("Registered parser for '$annotation' annotation");
            return new ParserBootstrapApi($annotation, $this->parserFactory);
        }

        /**
         * Register a new enricher
         *
         * @param string $name         Name of the enricher
         * @param string $description  A describing text
         *
         * @return EnricherBootstrapApi
         */
        public function registerEnricher($name, $description) {
            $this->logger->log("Registered enricher '$name'");
            $this->enrichers[$name] = $description;
            return new EnricherBootstrapApi($name, $this->enricherFactory);
        }
    }
}
