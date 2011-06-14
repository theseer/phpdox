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

namespace TheSeer\phpDox\DocBlock {

    class GenericElement {

        protected $factory;

        protected $name;
        protected $body;
        protected $attributes = array();

        public function __construct(\TheSeer\phpDox\FactoryInterface $factory, $name) {
            $this->factory = $factory;
            $this->name = $name;
        }

        public function getAnnotationName() {
            return $this->name;
        }

        public function getBody() {
            return $this->body;
        }

        public function __call($method, $value) {
            if (!preg_match('/^set/', $method)) {
                throw new GenericElementException("Method '$method' not defined", GenericElementException::MethodNotDefined);
            }
            // extract attribute name (remove 'set' or 'get' from string)
            $attribute = strtolower(substr($method, 3));
            $this->attributes[$attribute] = $value[0];
        }

        public function setBody($body) {
            $this->body = $body;
        }

        public function asDom(\TheSeer\fDOM\fDOMDocument $ctx) {
            $node = $ctx->createElementNS('http://xml.phpdox.de/src#', $this->name);
            foreach($this->attributes as $attribute => $value) {
                $node->setAttribute($attribute, $value);
            }
            if ($this->body !== null && $this->body !== '') {
                $parser = $this->factory->getInstanceFor('InlineProcessor', $ctx);
                $node->appendChild($parser->transformToDom($this->body));
            }
            return $node;
        }

    }

    class GenericElementException extends \Exception {
        const MethodNotDefined = 1;
        const PropertyNotDefined = 2;
    }
}