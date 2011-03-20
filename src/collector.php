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

      protected $xmlDir;

      protected $publicOnly = false;

      protected $namespaces;
      protected $interfaces;
      protected $classes;

      /**
       * Collector constructor
       *
       * @param string 		 $xmlDir	Base path to store individual class files in
       * @param fDomDocument $nsDom		DOM instance to register namespaces in
       * @param fDomDocument $iDom		DOM instance to register interfaces in
       * @param fDomDocument $cDom		DOM instance to register classes in
       */
      public function __construct($xmlDir, fDOMDocument $nsDom, fDOMDocument $iDom, fDOMDocument $cDom) {
         $this->xmlDir     = $xmlDir;
         $this->namespaces = $nsDom;
         $this->interfaces = $iDom;
         $this->classes    = $cDom;
      }

      public function setPublicOnly($switch) {
         $this->publicOnly = $switch === true;
      }

      /**
       * Main executer of the collector, looping over the iterator with found files
       *
       * @param \Iterator $scanner
       * @param Logger    $logger
       */
      public function run(\Theseer\Tools\IncludeExcludeFilterIterator $scanner, $logger) {

         $worker  = new PHPFilterIterator($scanner);
         $builder = new Builder($this->publicOnly);

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
               $xml = $builder->processFile($file);
               $xml->formatOutput= true;
               $xml->save($target);
               touch($target, $file->getMTime(), $file->getATime());

               $src = realpath($file->getPathName());

               $this->registerNamespaces($target, $src, $builder->getNamespaces());
               $this->registerInContainer($this->interfaces, 'interface', $target, $src, $builder->getInterfaces());
               $this->registerInContainer($this->classes, 'class', $target, $src, $builder->getClasses());
               $logger->progress('processed');
            } catch (\Exception $e) {
               $logger->progress('failed');
               var_dump($e);
               // TODO: Report Exception ;)
            }
         }

         $logger->buildSummary();
      }

      protected function registerNamespaces($target, $src, array $list) {
         foreach($list as $namespace) {
            $name = $namespace->getAttribute('name');
            $nsNode = $this->namespaces->query("//phpdox:namespace[@name='$name']")->item(0);
            if (!$nsNode) {
               $nsNode = $this->namespaces->documentElement->appendElementNS('http://phpdox.de/xml#','namespace');
               $nsNode->setAttribute('name', $name);
            }
            $fNode = $this->namespaces->query("//phpdox:namespace[@name='$name']/phpdox:file[@src='$src']")->item(0);
            if (!$fNode) {
               $file = $nsNode->appendElementNS('http://phpdox.de/xml#','file');
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
            if ($srcNode->parentNode->localName=='namespace') {
               $ns = $srcNode->parentNode->getAttribute('name');
               $ctx = $container->query("//phpdox:namespace[@name='$ns']")->item(0);
               if (!$ctx) {
                  $ctx = $container->documentElement->appendElementNS('http://phpdox.de/xml#','namespace');
                  $ctx->setAttribute('name', $srcNode->parentNode->getAttribute('name'));
               }
            } else {
               $ctx = $container->documentElement;
            }
            $workNode = $ctx->appendElementNS('http://phpdox.de/xml#',$nodeName);
            foreach($srcNode->attributes as $attr) {
               $workNode->appendChild($container->importNode($attr,true));
            }
            $workNode->setAttribute('xml', substr($target, strlen($this->xmlDir)+1));
            $workNode->setAttribute('src', $src);
         }
      }

      protected function setupTarget($file) {
         $path = array();
         foreach(explode('/', $file->getPathName()) as $part) {
            if($part == '.' || $part == '') continue;
            $path[] = $part;
         }
         $target = $this->xmlDir . '/' . join('/',$path).'.xml';
         $targetDir = dirname($target);
         clearstatcache();
         if (!file_exists($targetDir)) {
            mkdir($targetDir,0755,true);
         }
         return $target;
      }
   }
}