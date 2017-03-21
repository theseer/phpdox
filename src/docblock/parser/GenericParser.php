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

    class GenericParser {

        protected $factory;
        protected $aliasMap;
        protected $name;
        protected $payload;

        private $types = array(
            '', 'null', 'mixed', '{unknown}', 'object', 'array', 'integer', 'int',
            'float', 'string', 'boolean', 'resource'
        );

        public function __construct(Factory $factory, $name) {
            $this->factory = $factory;
            $this->name = $name;
        }

        public function setAliasMap(array $map) {
            $this->aliasMap = $map;
        }

        public function setPayload($payload) {
            $this->payload = trim($payload);
        }

        public function getObject(array $buffer) {
            $obj = $this->buildObject('generic', $buffer);
            $obj->setValue($this->payload);
            return $obj;
        }

        protected function buildObject($classname, array $buffer) {
            $obj = $this->factory->getElementInstanceFor($classname, $this->name);
            if (count($buffer)) {
                $obj->setBody(trim(join("\n", $buffer)));
            }
            return $obj;
        }

        protected function lookupType($type) {
            if ($type === 'self' || $type === 'static') {
                return $this->aliasMap['::unit'];
            }

            // Do not mess with scalar and fixed types
            if (in_array($type, $this->types)) {
                return $type;
            }

            // absolute definition?
            if ($type[0] == '\\') {
                return $type;
            }

            // alias?
            if (isset($this->aliasMap[$type])) {
                return $this->aliasMap[$type];
            }

            // relative to local namespace?
            if (isset($this->aliasMap['::context'])) {
                return $this->aliasMap['::context'] . '\\' . $type;
            }

            // don't know any better ..
            return  $type;
        }

    }

}
