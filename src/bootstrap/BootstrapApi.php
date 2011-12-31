<?php
/**
 * Copyright (c) 2010-2011 Arne Blankerts <arne@blankerts.de>
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

    use TheSeer\phpDox\DocBlock\Factory as DocBlockFactory;
    use TheSeer\phpDox\Engine\Factory as EngineFactory;

    class BootstrapApi {

        /**
         * Reference to the EngineFactory instance
         *
         * @var EngineFactory
         */
        protected $engineFactory;

        /**
         * Reference to the DocblockParserFactory instance
         *
         * @var DocBlockFactory
         */
        protected $parserFactory;


        /**
         * Array of registered engines
         *
         * @var array
         */
        protected $engines = array();

        /**
         * Constructor
         *
         * @param FactoryInterface $factory
         */
        public function __construct(EngineFactory $ef, DocBlockFactory $df, ProgressLogger $logger) {
            $this->engineFactory = $ef;
            $this->parserFactory = $df;
            $this->logger = $logger;
        }

        public function getEngines() {
            return $this->engines;
        }

        public function registerEngine($name, $description) {
            $this->logger->log("Registered output engine '$name'");
            $this->engines[$name] = $description;
            return new EngineBootstrapApi($name, $this->engineFactory);
        }

        public function registerParser($annotation) {
            $this->logger->log("Registered parser for '$annotation' annotation");
            return new ParserBootstrapApi($annotation, $this->parserFactory);
        }

    }
}