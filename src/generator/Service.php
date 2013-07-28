<?php
/**
 * Copyright (c) 2010-2013 Arne Blankerts <arne@blankerts.de>
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
namespace TheSeer\phpDox\Generator {

    use \TheSeer\fDom\fDomDocument;
    use \TheSeer\fDom\fDomElement;

    class Service {

        protected $generator;
        protected $container;

        public function __construct(AbstractGenerator $generator, Container $container) {
            $this->generator  = $generator;
            $this->namespaces = $container->getDocument('namespaces');
            $this->interfaces = $container->getDocument('interfaces');
            $this->classes    = $container->getDocument('classes');
        }

        public function getClass($classname) {
            $q = "//phpdox:class[@full=" . $classname . '"]';
            $node = $this->classes->queryOne($q);
            $dom = $this->generator->loadDataFile($node->getAttribute('xml'));
            return $dom->queryOne($q);
        }

        public function getClassInheritance($classname) {
            return $this->resolveInheritance($this->classes, 'class', $classname);
        }

        public function getInterfaceInheritance($interface) {
            return $this->resolveInheritance($this->interfaces, 'interface', $interface);
        }

        protected function resolveInheritance(fDOMDocument $source, $nodename, $name) {
            $dom = new fDOMDocument();
            $frag = $dom->createDocumentFragment();
            $q = '//phpdox:'.$nodename.'[@full=:name]';
            $ctx = $frag;
            $xp = $source->getDOMXPath();
            $node = $source->queryOne($xp->prepare($q, array('name' => $name)));
            while ($node) {
                $res = $ctx->appendChild($dom->importNode($node));
                if ($node->hasChildNodes()) {
                    $extends = $node->queryOne('phpdox:extends');
                    $classname = $extends->getAttribute('full');
                    $node = $source->queryOne($xp->prepare($q, array('name' => $name)));
                    if (!$node) {
                        $res->appendChild($dom->importNode($extends));
                        break;
                    }
                    $ctx = $res;
                    continue;
                }
                break;
            }
            return $frag;
        }

        /**
         * Resolve see annotation to mentioned class or method
         *
         * @param $see
         *
         * @todo Actually implement :)
         */
        public function resolveSee($see) {
        }

    }
}