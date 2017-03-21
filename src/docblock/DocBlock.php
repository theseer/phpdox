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

namespace TheSeer\phpDox\DocBlock {

    class DocBlock {

        protected $elements = array();

        public function appendElement(GenericElement $element) {
            $name = $element->getAnnotationName();
            if (isset($this->elements[$name])) {
                if (!is_array($this->elements[$name])) {
                    $this->elements[$name] = array($this->elements[$name]);
                }
                $this->elements[$name][] = $element;
                return;
            }
            $this->elements[$name] = $element;
        }

        public function hasElementByName($name) {
            return isset($this->elements[$name]);
        }

        public function getElementByName($name) {
            if (!isset($this->elements[$name])) {
                throw new DocBlockException("No element with name '$name'", DocBlockException::NotFound);
            }
            return $this->elements[$name];
        }

        /**
         * @param \TheSeer\fDOM\fDOMDocument $doc
         * @return \TheSeer\fDOM\fDOMElement
         */
        public function asDom(\TheSeer\fDOM\fDOMDocument $doc) {
            $node = $doc->createElementNS('http://xml.phpdox.net/src', 'docblock');
            // add lines and such?
            foreach($this->elements as $element) {
                if (is_array($element)) {
                    foreach($element as $el) {
                        $node->appendChild($el->asDom($doc));
                    }
                    continue;
                }
                $node->appendChild($element->asDom($doc));
            }
            return $node;
        }

    }

}
