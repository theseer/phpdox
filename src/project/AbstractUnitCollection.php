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
     */
namespace TheSeer\phpDox\Project {

    use TheSeer\fDOM\fDOMDocument;

    /**
     *
     */
    abstract class AbstractUnitCollection implements DOMCollectionInterface {

        /**
         * @var array
         */
        private $units = array();


        private $dom;

        /**
         * @var string
         */
        protected $collectionName;

        /**
         * @param \TheSeer\fDOM\fDOMDocument $dom
         * @return void
         */
        public function import(fDOMDocument $dom) {
            $this->dom = $dom;
            $this->dom->registerNamespace('phpdox', 'http://xml.phpdox.de/src#');
        }

        /**
         * This method exports all newly registered units into their respective files
         * and updates the collection file accordingly
         *
         * @param string $xmlDir
         *
         * @return \TheSeer\fDOM\fDOMDocument
         */
        public function export($xmlDir) {
            if ($this->dom instanceof fDOMDocument) {
                $root = $this->dom->documentElement;
            } else {
                $this->dom = new fDOMDocument('1.0', 'UTF-8');
                $this->dom->registerNamespace('phpdox', 'http://xml.phpdox.de/src#');
                $root = $this->dom->appendElementNS('http://xml.phpdox.de/src#', $this->collectionName);
            }
            foreach($this->units as $x => $unit) {
                $unitNode = $this->dom->importNode($unit->export($xmlDir . '/' . $this->collectionName), true);
                $ctx = $root->queryOne('phpdox:namespace[@name="' . $unitNode->getAttribute('namespace') . '"]');
                if (!$ctx) {
                    $ctx = $root->appendElementNS('http://xml.phpdox.de/src#', 'namespace');
                    $ctx->setAttribute('name', $unitNode->getAttribute('namespace'));
                }
                $full = $unitNode->getAttribute('full');
                if ($old = $ctx->queryOne("*[@full = '{$full}']")) {
                    $ctx->replaceChild($unitNode, $old);
                } else {
                    $ctx->appendChild($unitNode);
                }
            }
            $this->dom->formatOutput = TRUE;
            $this->dom->preserveWhiteSpace = TRUE;
            $this->dom->save($xmlDir . '/' . $this->collectionName . '.xml');
            return $this->dom;
        }

        /**
         * @param AbstractUnitObject $unit
         */
        protected function addUnit(AbstractUnitObject $unit) {
            $this->units[$unit->getName()] = $unit;
        }
    }

}
