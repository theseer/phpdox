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

    class Parser {

        protected $map = array(
            'docblock' => 'TheSeer\\phpDox\\DocBlock\\DocBlock',

            'invalid' => 'TheSeer\\phpDox\\DocBlock\\InvalidParser',
            'generic' => 'TheSeer\\phpDox\\DocBlock\\GenericParser',

            'description' => 'TheSeer\\phpDox\\DocBlock\\DescriptionParser',
            'param' => 'TheSeer\\phpDox\\DocBlock\\ParamParser',
            'var' => 'TheSeer\\phpDox\\DocBlock\\VarParser',
            'return' => 'TheSeer\\phpDox\\DocBlock\\VarParser',
            'license' => 'TheSeer\\phpDox\\DocBlock\\LicenseParser'
            );

        protected $current;

        public function __construct(array $map = array()) {
            $this->map = array_merge($this->map, $map);
        }

        public function parse($block) {
            $docBlock = new $this->map['docblock']();
            $lines = $this->prepare($block);
            $this->startParser('description');
            $buffer = array();
            foreach($lines as $line) {
                if ($line == '' || $line == '/') {
                    if (count($buffer)) {
                        $buffer[] = '';
                    }
                    continue;
                }

                if ($line[0]=='@') {
                    $docBlock->appendElement(
                        $this->current->getObject($buffer)
                    );
                    $buffer = array();

                    $lineParts = explode(' ', ltrim($line, '@'), 2);
                    $name      = $lineParts[0];
                    $payload   = ( isset( $lineParts[1] ) ? $lineParts[1] : '' );

                    $this->startParser($name, $payload);
                    continue;
                }
                $buffer[] = $line;
            }
            $docBlock->appendElement(
                $this->current->getObject($buffer)
            );
            return $docBlock;
        }

        protected function prepare($block) {
            $block = str_replace(array("\r\n","\r"), "\n", $block);
            $raw = array();
            foreach(explode("\n", $block) as $line) {
                $raw[] = substr(trim($line, " \n\t"), 2);
            }
            return $raw;
        }

        protected function startParser($name, $payload = NULL) {
            if (!preg_match('/^[a-zA-Z0-9]*$/', $name)) {
                // TODO: errorlog
                $this->current = new $this->map['invalid']($name);
            } else {
                if (isset($this->map[$name])) {
                    $this->current = new $this->map[$name]($name);
                } else {
                    $this->current = new $this->map['generic']($name);
                }
            }
            if ($payload !== NULL) {
                $this->current->setPayload($payload);
            }
        }

    }

}