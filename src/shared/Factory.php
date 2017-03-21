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

    use TheSeer\DirectoryScanner\DirectoryScanner;
    use TheSeer\phpDox\Collector\Collector;
    use TheSeer\phpDox\Collector\InheritanceResolver;
    use TheSeer\phpDox\Collector\Project;
    use TheSeer\phpDox\Generator\Engine\EventHandlerRegistry;
    use TheSeer\phpDox\Generator\Generator;

    /**
     *
     */
    class Factory {

        /**
         * @var FileInfo
         */
        private $homeDir;

        /**
         * @var Version
         */
        private $version;

        /**
         * @var array
         */
        private $instances = array();

        /**
         * @var bool
         */
        private $isSilentMode = false;

        /**
         * @param array $map
         */
        public function __construct(FileInfo $home, Version $version) {
            $this->homeDir = $home;
            $this->version = $version;
        }

        public function activateSilentMode() {
            $this->isSilentMode = true;
        }

        /**
         * @return ErrorHandler
         */
        public function getErrorHandler() {
            return new ErrorHandler($this->version);
        }

        /**
         * @return CLI
         */
        public function getCLI() {
            return new CLI(new Environment(), $this->version, $this);
        }

        /**
         * @return ConfigLoader
         */
        public function getConfigLoader() {
            return new ConfigLoader($this->version, $this->homeDir);
        }

        public function getConfigSkeleton() {
            return new ConfigSkeleton(
                new FileInfo(__DIR__ . '/../config/skeleton.xml')
            );
        }

        /**
         * @return DirectoryScanner
         */
        private function getDirectoryScanner() {
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
        private function getBootstrapApi() {
            return new BootstrapApi($this->getBackendFactory(), $this->getDocblockFactory(), $this->getEnricherFactory(), $this->getEngineFactory(), $this->getLogger());
        }

        /**
         * @return ProgressLogger
         */
        public function getLogger() {
            if (!isset($this->instances['logger'])) {
                $this->instances['logger'] = $this->isSilentMode ? $this->getSilentProgressLogger() : $this->getShellProgressLogger();
            }
            return $this->instances['logger'];
        }

        private function getSilentProgressLogger() {
            return new SilentProgressLogger();
        }

        private function getShellProgressLogger() {
            return new ShellProgressLogger();
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
            $scanner->setFlag(\FilesystemIterator::UNIX_PATHS);

            return $scanner;
        }

        /**
         * @param CollectorConfig $config
         *
         * @return Collector
         */
        public function getCollector(CollectorConfig $config) {
            return new Collector(
                $this->getLogger(),
                new Project(
                    $config->getSourceDirectory(),
                    $config->getWorkDirectory()
                ),
                $this->getBackendFactory()->getInstanceFor($config->getBackend()),
                $config->getFileEncoding()
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

}
