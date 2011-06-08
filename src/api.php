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

    class API {

        /**
         * Refrence to the factory instance
         *
         * @var FactoryInterface
         */
        protected $factory;

        /**
         * Internal map of registered Builders with their classname or factory instance
         *
         * @var array
         */
        protected $builderMap = array();

        /**
         * Constructor
         *
         * @param FactoryInterface $factory
         */
        public function __construct(FactoryInterface $factory) {
            $this->factory = $factory;
        }

        /**
         * Getter to receive the map of registered Builders
         *
         * @return array
         */
        public function getBuilderMap() {
            return $this->builderMap;
        }

        /**
         * Register a new builder with its name, title and factory instance to use to instance it
         *
         * @param string           $name    Name of the builder
         * @param string           $title   Internal title used on display
         * @param FactoryInterface $factory Factory to instantiate the builder with
         */
        public function registerBuilderFactory($name, $title, FactoryInterface $factory) {
            $this->factory->addFactory($name, $factory);
            $this->builderMap[$name] = $title;
        }

        /**
         * Register a new builder with its name, title and classname
         *
         * @param string $name  Name of the builder
         * @param string $title Internal title used on display
         * @param string $class Classname to instantiate
         */
        public function registerBuilderClass($name, $title, $class) {
            $this->factory->addClass($name, $class);
            $this->builderMap[$name] = $title;
        }

        public function registerParserFactory($annotation, FactoryInterface $factory) {
            $this->factory->getInstanceFor('DocblockFactory')->addParserFactory($annotation, $factory);
        }

        public function registerParserClass($annotation, $class) {
            $this->factory->getInstanceFor('DocblockFactory')->addParserClass($annotation, $class);
        }

    }
}