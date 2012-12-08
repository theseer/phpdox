<?php
/**
 * Copyright (c) 2010-2012 Arne Blankerts <arne@blankerts.de>
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
    use TheSeer\phpDox\Generator\Generator;
    use TheSeer\phpDox\Collector\Collector;
    use TheSeer\phpDox\Collector\ClassBuilder;

    class Factory implements FactoryInterface {

        protected $map = array(
            'DirectoryScanner' => '\\TheSeer\\DirectoryScanner\\DirectoryScanner',
            'ErrorHandler' => '\\TheSeer\\phpDox\\ErrorHandler',
            'EventFactory' => '\\TheSeer\\phpDox\\Generator\EventFactory',
            'ConfigLoader' => '\\TheSeer\\phpDox\\ConfigLoader'
        );

        protected $loggerMap = array(
            'silent' => '\\TheSeer\\phpDox\\ProgressLogger',
            'shell' => '\\TheSeer\\phpDox\\ShellProgressLogger'
        );

        protected $instances = array();
        protected $config;

        protected $loggerType = 'shell';
        protected $logger;

        public function __construct(array $map = null) {
            if ($map !== null) {
                $this->map = $map;
            }
        }

        public function setLoggerType($name) {
            if (!isset($this->loggerMap[$name])) {
                throw new FactoryException("No logger class for type '$name'", FactoryException::NoClassDefined);
            }
            $this->loggerType = $name;
        }

        public function addFactory($name, FactoryInterface $factory) {
            $this->map[$name] = $factory;
        }

        public function addClass($name, $class) {
            $this->map[$name] = $class;
        }

        public function getInstanceFor($name) {
            $params = func_get_args();
            if (isset($this->map[$name])) {
                if ($this->map[$name] instanceof FactoryInterface) {
                    return call_user_func_array( array($this->map[$name], 'getInstanceFor'), $params);
                }
                if (is_string($this->map[$name])) {
                    array_shift($params);
                    return $this->getGenericInstance($this->map[$name], $params);
                }
            }
            $method = 'get'.$name;
            array_shift($params);
            if (method_exists($this, $method)) {
                return call_user_func_array(array($this, $method), $params);
            }
            return $this->getGenericInstance($name, $params);
        }

        protected function getGenericInstance($class, array $params) {
            $rfc = new \ReflectionClass($class);
            if (!$rfc->isInstantiable()) {
                throw new FactoryException("class '$class' is not instantiable", FactoryException::NotInstantiable);
            }
            if (count($params)==0) {
                return new $class();
            }
            if (!$rfc->getConstructor()) {
               throw new FactoryException("class '$class' does not have a constructor but constructor parameters given", FactoryException::NoConstructor);
            }
            return $rfc->newInstanceArgs($params);
        }

        protected function getCLI() {
            return new CLI($this);
        }

        protected function getBootstrapApi() {
            return new BootstrapApi($this->getBackendFactory(), $this->getDocblockFactory(), $this->getEngineFactory(), $this->getLogger());
        }

        protected function getLogger() {
            if (!$this->logger) {
                $this->logger = new $this->loggerMap[$this->loggerType]();
            }
            return $this->logger;
        }

        protected function getBootstrap() {
            return new Bootstrap($this->getLogger(), $this->getBootstrapApi());
        }

        protected function getApplication() {
            return new Application($this, $this->getLogger());
        }

        protected function getResolver($xmlDir) {
            return new Resolver($xmlDir);
        }

        protected function getScanner($include, $exclude = null) {
            $scanner = $this->getInstanceFor('DirectoryScanner');

            if (is_array($include)) {
                $scanner->setIncludes($include);
            } else {
                $scanner->addInclude($include);
            }

            if ($exclude != null) {
                if (is_array($exclude)) {
                    $scanner->setExcludes($exclude);
                } else {
                    $scanner->addExclude($exclude);
                }
            }
            return $scanner;
        }

        protected function getCollector($srcDir, $xmlDir) {
            return new Collector($this->getLogger(), new \TheSeer\phpDox\Project\Project($srcDir, $xmlDir));
        }

        protected function getInheritanceResolver() {
            return new \TheSeer\phpDox\Collector\InheritanceResolver($this->getLogger());
        }

        protected function getGenerator() {
            return new Generator($this->getInstanceFor('EventFactory'), $this->getLogger());
        }

        protected function getClassBuilder(array $aliasMap, $public, $encoding) {
            return new ClassBuilder($this->getDocblockParser(), $aliasMap, $public, $encoding);
        }

        protected function getService(AbstractGenerator $generator) {
            return new Service($generator, $this->getContainer($generator->getXMLDirectory()));
        }

        protected function getDocblockFactory() {
            if (!isset($this->instances['DocblockFactory'])) {
                $this->instances['DocblockFactory'] = new \TheSeer\phpDox\DocBlock\Factory();
            }
            return $this->instances['DocblockFactory'];
        }

        protected function getBackendFactory() {
            if (!isset($this->instances['BackendFactory'])) {
                $this->instances['BackendFactory'] = new \TheSeer\phpDox\Collector\Backend\Factory($this);
            }
            return $this->instances['BackendFactory'];
        }

        protected function getEngineFactory() {
            if (!isset($this->instances['EngineFactory'])) {
                $this->instances['EngineFactory'] = new \TheSeer\phpDox\Generator\Engine\Factory();
            }
            return $this->instances['EngineFactory'];
        }

        protected function getDocblockParser() {
            if (!isset($this->instances['DocblockParser'])) {
                $this->instances['DocblockParser'] = new \TheSeer\phpDox\DocBlock\Parser($this->getDocblockFactory());
            }
            return $this->instances['DocblockParser'];
        }

    }

    class FactoryException extends \Exception {
        const NoClassDefined = 1;
        const NotInstantiable = 2;
        const NoConstructor = 3;
    }

}