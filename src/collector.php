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
 */
namespace TheSeer\phpDox {

    use \TheSeer\Tools\PHPFilterIterator;
    use \TheSeer\fDOM\fDOMDocument;

    class Collector {

        /**
         * Starting index position in src path string to use in store
         * @var int
         */
        protected $srcIndex = 0;

        /**
         * Path to store xml work data in
         * @var string
         */
        protected $xmlDir;

        /**
         * Flag to enable or disable processing of non public methods and members
         * @var boolean
         */
        protected $publicOnly = false;

        /**
         * fDOMDocument used to register Namespaces in
         * @var \TheSeer\fDOM\fDOMDocument
         */
        protected $namespaces;

        /**
         * fDOMDocument used to register Packages in
         * @var \TheSeer\fDOM\fDOMDocument
         */
        protected $packages;

        /**
         * fDOMDocument used to register Interfaces in
         * @var \TheSeer\fDOM\fDOMDocument
         */
        protected $interfaces;

        /**
         * fDOMDocument used to register classes in
         * @var \TheSeer\fDOM\fDOMDocument
         */
        protected $classes;

        /**
         * Collector constructor
         *
         * @param \TheSeer\fDOM\fDomDocument $nsDom	 DOM instance to register namespaces in
         * @param \TheSeer\fDOM\fDomDocument $pDom   DOM instance to register packages in
         * @param \TheSeer\fDOM\fDomDocument $iDom	 DOM instance to register interfaces in
         * @param \TheSeer\fDOM\fDomDocument $cDom	 DOM instance to register classes in
         */
        public function __construct($xmlDir, fDOMDocument $nsDom, fDOMDocument $pDom, fDOMDocument $iDom, fDOMDocument $cDom) {
            $this->xmlDir     = $xmlDir;
            $this->namespaces = $nsDom;
            $this->packages   = $pDom;
            $this->interfaces = $iDom;
            $this->classes    = $cDom;
        }

        /**
         * Setter to enable or disable handling of only public methods and members
         *
         * @param boolean $switch
         */
        public function setPublicOnly($switch) {
            $this->publicOnly = $switch === true;
        }

        /**
         * Setter to overwrite the default source directory string index position
         *
         * @param string $dir Directory to change source directory to
         */
        public function setStartIndex($index) {
            $this->srcIndex = $index;
        }

        /**
         * Main executer of the collector, looping over the iterator with found files
         *
         * @param \Iterator      $scanner Iterator with splFileObjects
         * @param ProgressLogger $logger  A Logger instance to report progress and problems
         */
        public function run(\Theseer\Tools\IncludeExcludeFilterIterator $scanner, $logger) {
            $worker = new PHPFilterIterator($scanner);
            $analyser = new Analyser($this->publicOnly);

            if (!file_exists($this->xmlDir)) {
                mkdir($this->xmlDir);
            }

            foreach($worker as $file) {
                $target = $this->setupTarget($file);
                if (file_exists($target) && filemtime($target)==$file->getMTime()) {
                    $logger->progress('cached');
                    continue;
                }
                try {
                    $xml = $analyser->processFile($file);
                    $xml->formatOutput= true;
                    $xml->save($target);
                    touch($target, $file->getMTime(), $file->getATime());

                    $src = realpath($file->getPathName());

                    $this->registerNamespaces($target, $src, $analyser->getNamespaces());
                    $this->registerInContainer($this->packages, 'package', $target, $src, $analyser->getPackages());
                    $this->registerInContainer($this->interfaces, 'interface', $target, $src, $analyser->getInterfaces());
                    $this->registerInContainer($this->classes, 'class', $target, $src, $analyser->getClasses());
                    $logger->progress('processed');
                } catch (\Exception $e) {
                    $logger->progress('failed');
                    var_dump($e);
                    // TODO: Report Exception ;)
                }
            }
            $logger->completed();
        }

        protected function registerNamespaces($target, $src, array $list) {
            foreach($list as $namespace) {
                $name = $namespace->getAttribute('name');
                $nsNode = $this->namespaces->query("//phpdox:namespace[@name='$name']")->item(0);
                if (!$nsNode) {
                    $nsNode = $this->namespaces->documentElement->appendElementNS('http://xml.phpdox.de/src#', 'namespace');
                    $nsNode->setAttribute('name', $name);
                }
                $fNode = $this->namespaces->query("//phpdox:namespace[@name='$name']/phpdox:file[@src='$src']")->item(0);
                if (!$fNode) {
                    $file = $nsNode->appendElementNS('http://xml.phpdox.de/src#', 'file');
                    $file->setAttribute('xml', $target);
                    $file->setAttribute('src', $src);
                }
            }
        }

        protected function registerInContainer(fDomDocument $container, $nodeName, $target, $src, $list) {
            foreach($container->query("//phpdox:*[@src='$src']") as $old) {
                $old->parentNode->removeChild($old);
            }
            foreach($list as $srcNode) {
                if ($srcNode->parentNode->localName == 'namespace') {
                    $ns = $srcNode->parentNode->getAttribute('name');
                    $ctx = $container->query("//phpdox:namespace[@name='$ns']")->item(0);
                    if (!$ctx) {
                        $ctx = $container->documentElement->appendElementNS('http://xml.phpdox.de/src#', 'namespace');
                        $ctx->setAttribute('name', $srcNode->parentNode->getAttribute('name'));
                    }
                } else {
                    $ctx = $container->documentElement;
                }
                $workNode = $ctx->appendElementNS('http://xml.phpdox.de/src#', $nodeName);
                foreach($srcNode->attributes as $attr) {
                    $workNode->appendChild($container->importNode($attr, true));
                }
                $workNode->setAttribute('xml', substr($target, strlen($this->xmlDir)+1));
                $workNode->setAttribute('src', $src);
            }
        }

        protected function setupTarget($file) {
            $path = substr($file->getPathName(), $this->srcIndex);
            $target = $this->xmlDir . $path . '.xml';
            $targetDir = dirname($target);
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            return $target;
        }
    }

}