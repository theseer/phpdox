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
         * Array of Container DOM Documents
         *
         * @var array
         */
        protected $container = array();

        /**
         * Map for builder names on actual classes to
         *
         * @var array
         */
        protected $builderMap = array(
            'html' => '\\TheSeer\\phpDox\HtmlBuilder'
        );

        /**
         * Constructor of PHPDox Application
         *
         * @param ProgressLogger $logger Instance of the ProgressLogger class
         * @param string         $xmlDir Directory where (generated) xml files are stored in
         */
        public function __construct(ProgressLogger $logger, $xmlDir) {
            $this->logger = $logger;
            $this->xmlDir = $xmlDir;
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
            $collector = new Collector(
                $this->xmlDir,
                $this->getContainerDocument('namespaces'),
                $this->getContainerDocument('packages'),
                $this->getContainerDocument('interfaces'),
                $this->getContainerDocument('classes')
            );
            $collector->setPublicOnly($publicOnly);
            $collector->setStartIndex(strlen(dirname($srcDir)));
            $collector->run($scanner, $this->logger);

            $this->cleanUp($srcDir);
            $this->saveContainer();
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

            $generator = new Generator(
                $this->xmlDir,
                $tplDir,
                $docDir,
                $this->getContainerDocument('namespaces'),
                $this->getContainerDocument('packages'),
                $this->getContainerDocument('interfaces'),
                $this->getContainerDocument('classes')
            );
            $generator->setPublicOnly($publicOnly);

            foreach($generate as $name) {
                if (!isset($this->builderMap[$name])) {
                    throw new ApplicationException("'$name' is not a registered generation backend", ApplicationException::UnkownBackend);
                }
                $classname = $this->builderMap[$name];
                $this->logger->log("Registering backend '$classname'");
                $backend = new $classname();
                $backend->setUp($generator);
            }
            $this->logger->log("Starting generator\n");
            $generator->run($this->logger);
            $this->logger->log('Generator process completed');
        }

        /**
         * Helper to load or create Container DOM Documents for namespaces, classes, interfaces, ...
         *
         * @param string $name name of the file (identical to root node)
         *
         * @return \TheSeer\fDom\fDomDocument
         */
        protected function getContainerDocument($name) {
            $fname = $this->xmlDir . '/' . $name .'.xml';
            if (isset($this->container[$fname])) {
                return $this->container[$fname];
            }
            $dom = new fDOMDocument('1.0', 'UTF-8');
            if (file_exists($fname)) {
                $dom->load($fname);
            } else {
                $rootNode = $dom->createElementNS('http://xml.phpdox.de/src#', $name);
                $dom->appendChild($rootNode);
            }
            $dom->registerNamespace('phpdox', 'http://xml.phpdox.de/src#');
            $dom->formatOutput = true;
            $this->container[$fname] = $dom;
            return $dom;
        }

        /**
         * Helper to save all known and (updated) container files.
         */
        protected function saveContainer() {
            foreach($this->container as $fname => $dom) {
                $dom->save($fname);
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
                $this->getContainerDocument('namespaces'),
                $this->getContainerDocument('classes'),
                $this->getContainerDocument('interfaces')
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
        const UnkownBackend = 1;
    }
}
