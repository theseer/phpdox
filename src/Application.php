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

    use \TheSeer\fDom\fDomDocument;

    class Application {

        /**
         * Logger for progress and error reporting
         *
         * @var Logger
         */
        protected $logger;

        /**
         * Base path xml files are stored in
         *
         * @var string
         */
        protected $xmlDir;

        /**
         * Helper class wrapping container DOMDocuments
         *
         * @var Container
         */
        protected $container = null;

        /**
         * Factory instance
         * @var Factory
         */
        protected $factory;

        /**
         * Map for builder names to generators and configs
         *
         * @var array
         */
        protected $builderMap = array();

        /**
         * Constructor of PHPDox Application
         *
         * @param Factory   $factory   Factory instance
         * @param ProgressLogger $logger Instance of the ProgressLogger class
         * @param Container $container Container instance, holding coleection DOMs
         * @param string    $xmlDir    Directory where (generated) xml files are stored in
         */
        public function __construct(Factory $factory, ProgressLogger $logger, Container $container, $xmlDir) {
            $this->factory = $factory;
            $this->logger = $logger;
            $this->xmlDir = $xmlDir;
            $this->container = $container;
        }

        /**
         * Load bootstrap files to register components and builder
         *
         * @param Array $require Array of files to require
         */
        public function loadBootstrap(Array $require) {
            $require = array_merge($require, glob(__DIR__ . '/bootstrap/*.php'));

            $phpDox = $this->factory->getInstanceFor('API', $this, $this->logger);

            $bootstrap = function($filename) use ($phpDox) {
                require $filename;
            };

            foreach($require as $file) {
                if (!file_exists($file) || !is_file($file)) {
                    throw new CLIException("Require file '$file' not found or not a file", CLIException::RequireFailed);
                }
                $this->logger->log("Loading additional bootstrap file '$file'");
                $bootstrap($file);
            }

            $this->builderMap = $phpDox->getBuilderMap();
        }

        /**
         * Get BuilderMap array with registered builders
         *
         * @return Array
         */
        public function getBuilderMap() {
            return $this->builderMap;
        }

        /**
         * Run collection process on given directory tree
         *
         * @param string           $srcDir     Base path of source tree
         * @param DirectoryScanner $scanner    A Directory scanner object to process
         * @param boolean          $publicOnly Flag to enable processing of only public methods and members
         *
         * @return void
         */
        public function runCollector($srcDir, $scanner, $publicOnly = false) {
            $this->logger->log("Starting collector\n");
            $collector = $this->factory->getInstanceFor('Collector');
            $collector->setPublicOnly($publicOnly);
            $collector->setStartIndex(strlen(dirname($srcDir)));
            $collector->run($scanner, $this->logger);

            $this->cleanUp($srcDir);
            $this->container->save();
            $this->logger->log('Collector process completed');
        }

        /**
         * Run Documentation generation process
         *
         * @param string  $generate   array of generator backends to run
         * @param string  $tplDir     base directory for templates
         * @param string  $docDir     Output directory to store documentation in
         * @param boolean $publicOnly Flag to enable processing of only public methods and members
         *
         * @return void
         */
        public function runGenerator($generate, $tplDir, $docDir, $publicOnly = false) {
            $this->logger->reset();

            $failed = array_diff($generate, array_keys($this->builderMap));
            if (count($failed)) {
               $list = join(',', $failed);
               throw new ApplicationException("'$list' is/are not registered builder(s)", ApplicationException::UnknownBackend);
            }

            $todo = array();
            foreach($generate as $name) {
                $cfg = $this->builderMap[$name];
                $generator = $cfg->getGenerator();
                if (!isset($todo[$generator])) {
                    $todo[$generator]=array();
                }
                $todo[$generator][$name] = $cfg;
            }

            foreach($todo as $execGenerator => $list) {
                $this->logger->log("Starting $execGenerator");
                $generator = $this->factory->getInstanceFor($execGenerator, $tplDir, $docDir);
                $generator->setPublicOnly($publicOnly);
                $generator->run($list, $this->logger);
                $this->logger->log("$execGenerator process completed");
            }
        }

        /**
         * Helper to cleanup
         *
         * @param string $srcDir Source directory to compare xml structure with
         */
        protected function cleanup($srcDir) {
            $worker = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->xmlDir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            $len = strlen($this->xmlDir);
            $srcPath = dirname($srcDir);

            $containers = array(
                $this->container->getDocument('namespaces'),
                $this->container->getDocument('classes'),
                $this->container->getDocument('interfaces')
            );

            $whitelist = array(
                $this->xmlDir . '/namespaces.xml',
                $this->xmlDir . '/classes.xml',
                $this->xmlDir . '/interfaces.xml'
            );

            foreach($worker as $fname => $file) {
                $fname = $file->getPathname();
                if (in_array($fname, $whitelist)) {
                    continue;
                }
                if ($file->isFile()) {
                    $srcFile = $srcPath . substr($fname, $len, -4);
                    if (!file_exists($srcFile)) {
                        unlink($fname);
                        foreach($containers as $dom) {
                            foreach($dom->query("//phpdox:*[@src='{$srcFile}']") as $node) {
                                $node->parentNode->removeChild($node);
                            }
                        }
                    }
                } elseif ($file->isDir()) {
                    $rmDir = $srcPath . substr($fname, $len);
                    if (!file_exists($rmDir)) {
                        rmdir($fname);
                    }
                }
            }
        }

    }

    class ApplicationException extends \Exception {
        const UnknownBackend = 1;
    }
}