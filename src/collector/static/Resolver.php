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
    use TheSeer\fDOM\fDOMDocument;

    class Resolver {

        protected $xmlDir;
        protected $classesDom;

        public function __construct($xmlDir) {
            $this->xmlDir = $xmlDir;
        }

        public function run(Container $container) {
            $this->classesDom = $container->getDocument('classes');
            $classes = $this->classesDom->query('//phpdox:class');
            foreach($classes as $classNode) {
                $dom = $this->initDocument($classNode);
                $this->process($dom, $classNode);
                $dom->save($this->xmlDir.'/classes/'. str_replace('\\','_', $classNode->getAttribute('full')) . '.xml');
            }
        }

        protected function process(fDOMDocument $dom, \DOMNode $node) {
            foreach($node->query('.//phpdox:extends') as $extends) {
                $full = $extends->getAttribute('full');
                $extNode = $this->classesDom->queryOne('//phpdox:class[@full="' . $full . '"]');
                if (!$extNode) {
                    /*
                     if ($this->isPHPClass($full)) {
                    // ...
                    }
                    */
                    continue;
                }
                $extDom = $this->initDocument($extNode);
                foreach($extDom->query('//phpdox:method[@visibility != "private" and @abstract="false"]|//phpdox:member[@visibility != "private"]|//phpdox:constant') as $import) {
                    if (!$dom->queryOne('//phpdox:'.$import->localName.'[@name="'.$import->getAttribute('name').'"]')) {
                        $imported = $dom->documentElement->appendChild(
                                $dom->importNode($import, true)
                        );
                        $imported->setAttribute('inherited', $full);
                        $imported->removeAttribute('start');
                        $imported->removeAttribute('end');
                    }
                }
                $this->process($dom, $extDom);
            }
        }

        protected function initDocument(fDOMElement $node) {
            $dom = new fDOMDocument();
            $dom->registerNamespace('phpdox', 'http://xml.phpdox.de/src#');

            $tmp = new fDOMDocument();
            $tmp->load($this->xmlDir. '/' . $node->getAttribute('xml'));
            $tmp->registerNamespace('phpdox', 'http://xml.phpdox.de/src#');

            $dom->appendChild(
                $dom->importNode(
                    $tmp->queryOne('//phpdox:*[@full="' . $node->getAttribute('full') . '"]'),
                    true
                )
            );

            return $dom;
        }

    }
}