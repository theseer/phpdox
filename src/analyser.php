<?php
/**
 * Copyright (c) 2010 Arne Blankerts <arne@blankerts.de>
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

    use \pdepend\reflection\ReflectionSession;
    use \TheSeer\fDOM\fDOMDocument;

    class Analyser {

        protected $publicOnly;

        protected $namespaces;
        protected $interfaces;
        protected $classes;
        protected $packages;

        protected $dom;

        public function __construct($publicOnly = false) {
            $this->publicOnly = $publicOnly;
        }

        public function getClasses() {
            return $this->classes;
        }

        public function getInterfaces() {
            return $this->interfaces;
        }

        public function getNamespaces() {
            return $this->namespaces;
        }

        public function getPackages() {
            return $this->packages;
        }

        public function processFile(\SPLFileInfo $file) {
            $this->namespaces = array();
            $this->interfaces = array();
            $this->classes    = array();
            $this->packages   = array();

            $this->initWorkDocument($file);

            $session = new ReflectionSession();
            $session->addClassFactory( new \pdepend\reflection\factories\NullReflectionClassFactory() );
            $query = $session->createFileQuery();
            foreach ( $query->find( $file->getPathname() ) as $class ) {
                $this->handleClass($class);
            }

            return $this->dom;
        }

        protected function initWorkDocument(\SPLFileInfo $file) {
            $this->dom = new fDOMDocument('1.0', 'UTF-8');
            $this->dom->registerNamespace('dox', 'http://xml.phpdox.de/src#');
            $root = $this->dom->createElementNS('http://xml.phpdox.de/src#', 'file');
            $this->dom->appendChild($root);

            $head = $root->appendElementNS('http://xml.phpdox.de/src#', 'head');
            $head->setAttribute('path', $file->getPath());
            $head->setAttribute('file', $file->getFilename());
            $head->setAttribute('realpath', $file->getRealPath());
            $head->setAttribute('size', $file->getSize());
            $head->setAttribute('time', date('c', $file->getCTime()));
            $head->setAttribute('unixtime', $file->getCTime());
            $head->setAttribute('sha1', sha1_file($file->getPathname()));
        }

        protected function handleClass(\ReflectionClass  $class) {
            $context = $this->dom->documentElement;
            if ($class->inNamespace()) {
                $context = $this->handleNamespace($class);
            }

            $classBuilder = new ClassBuilder($context, $this->publicOnly);
            $classNode = $classBuilder->process($class);
            if ($package = $classBuilder->getPackage()) {
                $this->packages[$package] = $classNode;
            }
            if ($class->isInterface()) {
                $this->interfaces[$class->getName()] = $classNode;
            } else {
                $this->classes[$class->getName()] = $classNode;
            }
        }

        protected function handleNamespace(\ReflectionClass $class) {
            $namespace = $class->getNamespaceName();
            if (!isset($this->namespaces[$namespace])) {
                $nsNode = $this->dom->createElementNS('http://xml.phpdox.de/src#', 'namespace');
                $nsNode->setAttribute('name', $namespace);
                $this->dom->documentElement->appendChild($nsNode);
                $this->namespaces[$namespace] = $nsNode;
            }
            return $this->namespaces[$namespace];
        }

    }

}
