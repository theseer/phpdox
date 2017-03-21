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

    class Parser {

        protected $factory;
        protected $current = null;
        protected $aliasMap = array();

        public function __construct(Factory $factory) {
            $this->factory = $factory;
        }

        public function parse($block, array $aliasMap) {
            $this->aliasMap = $aliasMap;
            $this->current = null;

            $docBlock = $this->factory->getDocBlock();
            $lines = $this->prepare($block);
            if (count($lines)>1) {
                $this->startParser('description');
            }
            $buffer = array();
            foreach($lines as $line) {
                if ($line == '' || $line == '/') {
                    if (count($buffer)) {
                        $buffer[] = '';
                    }
                    continue;
                }

                if ($line[0]=='@') {
                    if ($this->current !== null) {
                        $docBlock->appendElement(
                            $this->current->getObject($buffer)
                        );
                    }
                    $buffer = array();

                    preg_match('/^\@([a-zA-Z0-9_]+)(.*)$/', $line, $lineParts);
                    $name      = ( isset( $lineParts[1] ) ? $lineParts[1] : '(undefined)');
                    $payload   = ( isset( $lineParts[2] ) ? trim($lineParts[2]) : '' );

                    $this->startParser($name, $payload);
                    continue;
                }
                $buffer[] = $line;
            }
            if (!$this->current) {
                // A Single line docblock with no @ annotation is considered a description
                $this->startParser('description');
            }
            $docBlock->appendElement(
                $this->current->getObject($buffer)
            );
            return $docBlock;
        }

        protected function prepare($block) {
            $block = str_replace(array("\r\n", "\r"), "\n", mb_substr($block, 3, -2));
            $raw = array();
            foreach(explode("\n", $block) as $line) {
                $line = preg_replace('/^\s*\*? ?/', '', $line);
                $raw[] = rtrim($line, " \n\t*");
            }
            return $raw;
        }

        protected function startParser($name, $payload = NULL) {
            if (!preg_match('/^[a-zA-Z0-9-_\.]*$/', $name) || empty($name)) {
                // TODO: errorlog
                $this->current = $this->factory->getParserInstanceFor('invalid', $name);
            } else {
                $this->current = $this->factory->getParserInstanceFor($name);
            }
            $this->current->setAliasMap($this->aliasMap);
            if ($payload !== NULL) {
                $this->current->setPayload($payload);
            }
        }

    }

}
