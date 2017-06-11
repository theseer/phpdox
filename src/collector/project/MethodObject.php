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
    use TheSeer\phpDox\DocBlock\DocBlock;

    /**
     *
     */
    class MethodObject {

        const XMLNS = 'http://xml.phpdox.net/src';

        /**
         * @var \TheSeer\fDOM\fDOMElement
         */
        private $ctx;

        /**
         * @var AbstractUnitObject
         */
        private $unit;

        /**
         * @param AbstractUnitObject $unit
         * @param fDOMElement        $ctx
         */
        public function __construct(AbstractUnitObject $unit, fDOMElement $ctx) {
            $this->unit = $unit;
            $this->ctx = $ctx;
        }

        public function getOwner() {
            return $this->unit;
        }

        public function export() {
            return $this->ctx;
        }

        /**
         * @param string $name
         */
        public function setName($name) {
            $this->ctx->setAttribute('name', $name);
        }

        public function getName() {
            return $this->ctx->getAttribute('name');
        }
        /**
         * @param int $startLine
         */
        public function setStartLine($startLine) {
            $this->ctx->setAttribute('start', $startLine);
        }

        /**
         * @param int $endLine
         */
        public function setEndLine($endLine) {
            $this->ctx->setAttribute('end', $endLine);
        }

        /**
         * @param boolean $isFinal
         */
        public function setFinal($isFinal) {
            $this->ctx->setAttribute('final', $isFinal ? 'true' : 'false');
        }

        /**
         * @param boolean $isAbstract
         */
        public function setAbstract($isAbstract) {
            $this->ctx->setAttribute('abstract', $isAbstract ? 'true' : 'false');
        }

        /**
         * @param boolean $isStatic
         */
        public function setStatic($isStatic) {
            $this->ctx->setAttribute('static', $isStatic ? 'true' : 'false');
        }

        /**
         * @param string $visibility
         */
        public function setVisibility($visibility) {
            if (!in_array($visibility, array('public','private','protected'))) {
                throw new MethodObjectException("'$visibility' is not valid'", MethodObjectException::InvalidVisibility);
            }
            $this->ctx->setAttribute('visibility', $visibility);
        }

        /**
         * @param DocBlock $docblock
         */
        public function setDocBlock(DocBlock $docblock) {
            $docNode = $docblock->asDom($this->ctx->ownerDocument);

            if ($this->ctx->hasChildNodes()) {
                $this->ctx->insertBefore($docNode, $this->ctx->firstChild);
                return;
            }
            $this->ctx->appendChild($docNode);
        }

        public function hasInheritDoc() {
            return $this->ctx->query('phpdox:docblock[@inherit="true"]')->length > 0;
        }

        public function inhertDocBlock(MethodObject $method) {
            $inherit = $method->export()->queryOne('phpdox:docblock');
            if (!$inherit) { // no docblock, no work ;)
                return;
            }
            $docNode = $this->ctx->queryOne('phpdox:docblock');
            if (!$docNode) {
                $this->setDocBlock(new DocBlock());
                $docNode = $this->ctx->queryOne('phpdox:docblock');
            }

            $container = $docNode->appendElementNS(self::XMLNS, 'inherited');
            $container->setAttribute(
                $method->getOwner()->getType(),
                $method->getOwner()->getName()
            );
            $container->appendChild($this->ctx->ownerDocument->importNode($inherit, true));

        }

        /**
         * @param string $name
         *
         * @return ReturnTypeObject
         */
        public function setReturnType($name) {
            $returnType = new ReturnTypeObject($this->ctx->appendElementNS(self::XMLNS, 'return'));
            $returnType->setType($name);
            return $returnType;
        }

        /**
         * @param string $name
         *
         * @return ParameterObject
         */
        public function addParameter($name) {
            $parameter = new ParameterObject($this->ctx->appendElementNS(self::XMLNS, 'parameter'));
            $parameter->setName($name);
            return $parameter;
        }

        /**
         * @param InlineComment $InlineComment
         */
        public function addInlineComment(InlineComment $InlineComment) {
            $this->getInlineContainer()->appendChild(
                $InlineComment->asDom($this->ctx->ownerDocument)
            );
        }

        /**
         * @return fDOMElement
         */
        private function getInlineContainer() {
            $node = $this->ctx->queryOne('phpdox:inline');
            if ($node !== NULL) {
                return $node;
            }
            return $this->ctx->appendElementNS(self::XMLNS, 'inline');
        }

    }

}
