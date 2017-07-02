<?php
    /**
     * Copyright (c) 2010-2017 Arne Blankerts <arne@blankerts.de>
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
namespace TheSeer\phpDox\Collector {

    use TheSeer\fDOM\fDOMElement;

    /**
     * Class AbstractVariableObject
     *
     * @package TheSeer\phpDox\Collector
     */
    abstract class AbstractVariableObject {

        const XMLNS = 'http://xml.phpdox.net/src';

        /**
         * @var \TheSeer\fDOM\fDOMElement
         */
        protected $ctx;

        /**
         * @var array
         */
        private $types = array('{unknown}', 'object', 'array', 'int', 'integer','float','string','bool','boolean','resource','callable');

        /**
         * @param fDOMElement $ctx
         */
        public function __construct(fDOMElement $ctx) {
            $this->ctx = $ctx;
        }

        /**
         * @return fDOMElement
         */
        public function export() {
            return $this->ctx;
        }

        /**
         * @param $line
         */
        public function setLine($line) {
            $this->ctx->setAttribute('line', $line);
        }

        /**
         * @return string
         */
        public function getLine() {
            return $this->ctx->getAttribute('line');
        }

        /**
         * @param $name
         */
        public function setName($name) {
            $this->ctx->setAttribute('name', $name);
        }

        /**
         * @return \DOMAttr
         */
        public function getName() {
            return $this->ctx->getAttributeNode('name');
        }

        /**
         * @param $value
         */
        public function setDefault($value) {
            $this->ctx->setAttribute('default', $value);
        }

        public function setConstant($const) {
            $this->ctx->setAttribute('constant', $const);
        }

        public function isInternalType($type) {
            return in_array(mb_strtolower($type), $this->types);
        }

        /**
         * @param $type
         */
        public function setType($type) {
            if (!$this->isInternalType($type)) {
                $parts = explode('\\', $type);
                $local = array_pop($parts);
                $namespace = join('\\', $parts);

                $unit = $this->ctx->appendElementNS(self::XMLNS, 'type');
                $unit->setAttribute('full', $type);
                $unit->setAttribute('namespace', $namespace);
                $unit->setAttribute('name', $local);
                $type = 'object';
            }
            $this->ctx->setAttribute('type', $type);
        }

        /**
         * @return string
         */
        public function getType() {
            return $this->ctx->getAttribute('type');
        }

        public function setNullable($isNullable) {
            $this->ctx->setAttribute('nullable', $isNullable ? 'true' : 'false');
        }

        protected function addInternalType($type) {
            $this->types[] = $type;
        }
    }
}
