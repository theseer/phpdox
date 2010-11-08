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

   use \TheSeer\Tools\PHPFilterIterator;
   use \TheSeer\fDOM\fDOMDocument;

   class Processor {

      protected $outputDir;

      protected $dom = array();
      protected $namespaces;
      protected $interfaces;
      protected $classes;

      /**
       * Constructor
       *
       * @param \eczConsoleOptions $input
       */
      public function __construct($outputDir) {
         $this->outputDir = $outputDir;
         $this->namespaces = $this->createContainerDocument('namespaces');
         $this->interfaces = $this->createContainerDocument('interfaces');
         $this->classes    = $this->createContainerDocument('classes');
      }

      public function __destruct() {
         foreach($this->dom as $name => $dom) {
            $dom->save($this->outputDir . '/' . $name . '.xml');
         }
      }

      /**
       * Main executer of the processor, looping over the iterator with found files
       *
       * @param \Iterator $scanner
       */
      public function run(\Theseer\Tools\IncludeExcludeFilterIterator $scanner) {

         $worker  = new PHPFilterIterator($scanner);
         $builder = new Builder();

         foreach($worker as $file) {
            $xml = $builder->processFile($file);
            $xml->formatOutput= true;
            $target = $this->setupTarget($file);
            $xml->save($target);
            touch($target, $file->getMTime(), $file->getATime());

            $this->registerNamespaces($target, $file->getPathName(), $builder->getNamespaces());
            $this->registerInterfaces($target, $file->getPathName(), $builder->getInterfaces());
            $this->registerClasses($target, $file->getPathName(), $builder->getClasses());
         }
      }

      protected function registerNamespaces($target, $src, $list) {
         $dom = $this->dom['namespaces'];
         foreach($list as $namespace) {
            $name = $namespace->getAttribute('name');
            $nsNode = $dom->query("//dox:namespace[@name='$name']")->item(0);
            if (!$nsNode) {
               $nsNode = $dom->createElementNS('http://phpdox.de/xml#','namespace');
               $nsNode->setAttribute('name', $name);
               $this->namespaces->appendChild($nsNode);
            }
            $file = $dom->createElementNS('http://phpdox.de/xml#','file');
            $file->setAttribute('xml', $target);
            $file->setAttribute('src', $src);
            $nsNode->appendChild($file);
         }
      }

      protected function registerInterfaces($target, $src, $list) {
      }

      protected function registerClasses($target, $src, $list) {
      }

      protected function setupTarget($file) {
         $path = array();
         foreach(explode('/', $file->getPathName()) as $part) {
            if($part == '.' || $part == '') continue;
            $path[] = $part;
         }
         $target = $this->outputDir . '/' . join('/',$path).'.xml';
         $targetDir = dirname($target);
         clearstatcache();
         if (!file_exists($targetDir)) {
            mkdir($targetDir,0755,true);
         }
         return $target;
      }

      protected function createContainerDocument($root) {
         $this->dom[$root] = new fDOMDocument('1.0', 'UTF-8');
         $this->dom[$root]->registerNamespace('dox', 'http://phpdox.de/xml#');
         $this->dom[$root]->formatOutput = true;
         $rootNode = $this->dom[$root]->createElementNS('http://phpdox.de/xml#', $root);
         $this->dom[$root]->appendChild($rootNode);
         return $rootNode;
      }
   }
}