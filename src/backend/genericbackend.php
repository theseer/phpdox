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

   use \TheSeer\fDom\fDomDocument;
   use \TheSeer\fXSL\fXSLTProcessor;
   use \TheSeer\fXSL\fXSLCallback;

   abstract class genericBackend {

      private $generator = NULL;
      private $xsltproc = array();

      /**
       * Internal kickof method from generator
       *
       * @param Generator $generator Reference to a generator instance for callbacks
       *
       */
      final public function run(Generator $generator) {
         $this->generator = $generator;
         try {
            $this->build();
         } catch(\Exception $e) {
            // TODO: do we need to do any cleanup or logging here?
            throw $e;
         }
      }

      /**
       * Entry point to main processing logic
       *
       */
      abstract public function build();

      /**
       * Helper to get XSLTProcessor instance
       *
       * This method also registers the public methods of
       * the backend to be callable from within the xsl context
       *
       * @param \DomDocument $xsl A Stylesheet DOMDocument
       *
       * @return TheSeer\fXSL\fXSLTProcessor
       */
      protected function getXSLTProcessor(\DomDocument $xsl) {
         $hash = spl_object_hash($xsl);
         if (isset($this->xsltproc[$hash])) {
            return $this->xsltproc[$hash];
         }

         $cb = new fXSLCallback('http://phpdox.de/callback', 'cb');
         $cb->setObject($this);
         $cb->setBlacklist(array('run','build'));

         $this->xsltproc[$hash] = new fXSLTProcessor($xsl);
         $this->xsltproc[$hash]->registerCallback($cb);

         return $this->xsltproc[$hash];
      }

      /**
       * Forwarder to get $generator->getClassesAsDOM
       *
       * @return TheSeer\fDom\fDomDocument
       */
      protected function getClassesAsDOM() {
         return $this->generator->getClassesAsDOM();
      }

      /**
       * Forwarder to get $generator->getNamespaces
       *
       * @return TheSeer\fDom\fDomDocument
       */
      protected function getNamespaceAsDOM() {
         return $this->generator->getNamespacesAsDOM();
      }

      public function getClasses() {
         static $classes = NULL;
         if ($classes === NULL) {
            foreach($this->getClassesAsDOM()->query('//phpdox:class/@full') as $f) {
               $classes[] = $f->nodeValue;
            }
         }
         return $classes;
      }

      public function getNamespaces() {
         static $namespaces = NULL;
         if ($namespaces === NULL) {
            foreach($this->getNamespacesAsDOM()->query('//phpdox:namespace/@name') as $n) {
               $namespaces[] = $n->nodeValue;
            }
         }
         return $namespaces;
      }

      public function getInterfaces() {
         static $interfaces = NULL;
         if ($interfaces === NULL) {
            foreach($this->getInterfacesAsDOM()->query('//phpdox:interface/@full') as $i) {
               $interfaces[] = $i->nodeValue;
            }
         }
         return $this->interfaces;
      }



      /**
       * Forwarder to get $generator->getInterfaces
       *
       * @return TheSeer\fDom\fDomDocument
       */
      protected function getInterfacesAsDOM() {
         return $this->generator->getInterfacesAsDOM();
      }


      /**
       * Helper to get the DomDocument for a given classname
       *
       * @param string $class Classname as string
       *
       * @return TheSeer\fDom\DomDocument
       */
      protected function getXMLByClassName($class) {
         $f = $this->generator->getClassesAsDOM()->query("//phpdox:class[@full='$class']")->item(0);
         if (!$f) {
            // return empty warning dom?
            throw new \Exception("Class '$class' not found");
         }
         $filename = $f->getAttribute('xml');
         $d = new fDomDocument();
         $d->load($this->generator->getXMLDirectory() . DIRECTORY_SEPARATOR . $filename);
         return $d;
      }

      protected function saveDomDocument($dom, $filename) {
         $filename = $this->generator->getDocumentationDirectory()
                   . DIRECTORY_SEPARATOR . $filename;
         $path = dirname($filename);
         clearstatcache();
         if (!file_exists($path)) {
            mkdir($path, 0755, true);
         }
         $dom->save($filename);
      }

      protected function classNameToFileName($class, $ext = 'xml') {
         return str_replace('\\','_', $class) . '.' . $ext;
      }

   }

}