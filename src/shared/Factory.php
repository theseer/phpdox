<?php
/**
 * Copyright (c) 2010-2014 Arne Blankerts <arne@blankerts.de>
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

    use TheSeer\DirectoryScanner\DirectoryScanner;
    use TheSeer\phpDox\Collector\InheritanceResolver;
    use TheSeer\phpDox\Generator\Engine\EventHandlerRegistry;
    use TheSeer\phpDox\Generator\Generator;
    use TheSeer\phpDox\Collector\Collector;

    /**
     *
     */
    class Factory {

        /**
         * @var array
         */
        private $map = array();

        /**
         * @var array
         */
        private $loggerMap = array(
            'silent' => '\\TheSeer\\phpDox\\ProgressLogger',
            'shell' => '\\TheSeer\\phpDox\\ShellProgressLogger'
        );

        /**
         * @var array
         */
        private $instances = array();

        /**
         * @var
         */
        private $config;

        /**
         * @var string
         */
        private $loggerType = 'shell';

        /**
         * @var ProgressLogger
         */
        private $logger;

        /**
         * @param array $map
         */
        public function __construct(array $map = NULL) {
            if ($map !== NULL) {
                $this->map = $map;
            }
        }

        /**
         * @param string $name
         * @throws FactoryException
         */
        public function setLoggerType($name) {
            if (!isset($this->loggerMap[$name])) {
                throw new FactoryException("No logger class for type '$name'", FactoryException::NoClassDefined);
            }
            $this->loggerType = $name;
        }


        /**
         * @return ErrorHandler
         */
        public function getErrorHandler() {
            return new ErrorHandler();
        }

        /**
         * @return CLI
         */
        public function getCLI() {
            return new CLI($this);
        }

        /**
         * @return ConfigLoader
         */
        public function getConfigLoader() {
            return new ConfigLoader();
        }

        /**
         * @return DirectoryScanner
         */
        public function getDirectoryScanner() {
            return new DirectoryScanner();
        }

        /**
         * @return DirectoryCleaner
         */
        public function getDirectoryCleaner() {
            return new DirectoryCleaner();
        }

        /**
         * @return BootstrapApi
         */
        public function getBootstrapApi() {
            return new BootstrapApi($this->getBackendFactory(), $this->getDocblockFactory(), $this->getEnricherFactory(), $this->getEngineFactory(), $this->getLogger());
        }

        /**
         * @return ProgressLogger
         */
        public function getLogger() {
            if (!$this->logger) {
                $this->logger = new $this->loggerMap[$this->loggerType]();
            }
            return $this->logger;
        }

        /**
         * @return Bootstrap
         */
        public function getBootstrap() {
            return new Bootstrap($this->getLogger(), $this->getBootstrapApi());
        }

        /**
         * @return Application
         */
        public function getApplication() {
            return new Application($this, $this->getLogger());
        }

        /**
         * @param string|array $include
         * @param string|array $exclude
         * @return mixed|object
         */
        public function getScanner($include, $exclude = NULL) {
            $scanner = $this->getDirectoryScanner();

            if (is_array($include)) {
                $scanner->setIncludes($include);
            } else {
                $scanner->addInclude($include);
            }

            if ($exclude != NULL) {
                if (is_array($exclude)) {
                    $scanner->setExcludes($exclude);
                } else {
                    $scanner->addExclude($exclude);
                }
            }
            return $scanner;
        }

        /**
         * @param FileInfo $srcDir
         * @param FileInfo $xmlDir
         * @return Collector
         */
        public function getCollector($srcDir, $xmlDir) {
            return new Collector(
                $this->getLogger(),
                new \TheSeer\phpDox\Collector\Project(
                    $srcDir, $xmlDir
                )
            );
        }

        /**
         * @return InheritanceResolver
         */
        public function getInheritanceResolver() {
            return new \TheSeer\phpDox\Collector\InheritanceResolver($this->getLogger());
        }

        /**
         * @return Generator
         */
        public function getGenerator() {
            return new Generator($this->getLogger(), new EventHandlerRegistry());
        }

        /**
         * @return \TheSeer\phpDox\DocBlock\Factory
         */
        public function getDocblockFactory() {
            if (!isset($this->instances['DocblockFactory'])) {
                $this->instances['DocblockFactory'] = new \TheSeer\phpDox\DocBlock\Factory($this);
            }
            return $this->instances['DocblockFactory'];
        }

        /**
         * @return mixed
         */
        public function getBackendFactory() {
            if (!isset($this->instances['BackendFactory'])) {
                $this->instances['BackendFactory'] = new \TheSeer\phpDox\Collector\Backend\Factory($this);
            }
            return $this->instances['BackendFactory'];
        }

        /**
         * @return mixed
         */
        public function getEngineFactory() {
            if (!isset($this->instances['EngineFactory'])) {
                $this->instances['EngineFactory'] = new \TheSeer\phpDox\Generator\Engine\Factory();
            }
            return $this->instances['EngineFactory'];
        }

        /**
         * @return Generator\Enricher\Factory
         */
        public function getEnricherFactory() {
            if (!isset($this->instances['EnricherFactory'])) {
                $this->instances['EnricherFactory'] = new \TheSeer\phpDox\Generator\Enricher\Factory();
            }
            return $this->instances['EnricherFactory'];
        }


        /**
         * @return mixed
         */
        public function getDocblockParser() {
            if (!isset($this->instances['DocblockParser'])) {
                $this->instances['DocblockParser'] = new \TheSeer\phpDox\DocBlock\Parser($this->getDocblockFactory());
            }
            return $this->instances['DocblockParser'];
        }

    }

    /**
     *
     */
    class FactoryException extends \Exception {

        /**
         *
         */
        const NoClassDefined = 1;
        /**
         *
         */
        const NotInstantiable = 2;
        /**
         *
         */
        const NoConstructor = 3;
    }

}
