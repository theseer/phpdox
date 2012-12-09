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
    class IndexCollection implements DOMCollectionInterface {

        /**
         * @var array
         */
        private $addedUnits = array();

        private $dom;

        /**
         * @var string
         */
        protected $collectionName;


        private function getRootElement() {
            if (!$this->dom instanceof fDOMDocument) {
                $this->initDomDocument();
            }
            return $this->dom->documentElement;
        }

        private function initDomDocument() {
            $this->dom = new fDOMDocument('1.0', 'UTF-8');
            $this->dom->registerNamespace('phpdox', 'http://xml.phpdox.de/src#');
            $this->dom->appendElementNS('http://xml.phpdox.de/src#', 'index');
        }

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
        public function export() {
            if (!$this->dom instanceof fDOMDocument) {
                $this->initDomDocument();
            }
            return $this->dom;
        }

        /**
         * @param ClassObject $class
         */
        public function addClass(ClassObject $class) {
            $this->addUnit($class,'class');
        }

        /**
         * @param InterfaceObject $interface
         */
        public function addInterface(InterfaceObject $interface) {
            $this->addUnit($interface, 'interface');
        }

        /**
         * @param TraitObject $trait
         */
        public function addTrait(TraitObject $trait) {
            $this->addUnit($trait, 'trait');
        }

        public function getAddedUnits() {
            return $this->addedUnits;
        }

        /**
         * @param $path
         * @return \DOMNodeList
         */
        public function getUnitsBySrcFile($path) {
            return $this->getRootElement()->query(sprintf('//*[@src="%s"]',$path));
        }

        /**
         * @param AbstractUnitObject $unit
         */
        protected function addUnit(AbstractUnitObject $unit, $type) {
            $root = $this->getRootElement();
            $this->addedUnits[$unit->getFullName()] = $unit;

            $unitNode = $root->appendElementNS('http://xml.phpdox.de/src#', $type);
            $unitNode->setAttribute('name', $unit->getName());
            $unitNode->setAttribute('src', $unit->getSourceFilename());

            $xpath = 'phpdox:namespace[@name="' . $unit->getNamespace() . '"]';
            $ctx = $root->queryOne($xpath);
            if (!$ctx) {
                $ctx = $root->appendElementNS('http://xml.phpdox.de/src#', 'namespace');
                $ctx->setAttribute('name', $unit->getNamespace());
            }
            $name = $unit->getName();
            if ($old = $ctx->queryOne("*[@name = '{$name}']")) {
                $ctx->replaceChild($unitNode, $old);
            } else {
                $ctx->appendChild($unitNode);
            }
        }

    }

}
