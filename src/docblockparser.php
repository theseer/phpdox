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

      protected $map;

      protected $context;

      public function __construct(array $map = array()) {
         $this->map = $map;
      }

      public function parse($block) {
         $docBlock = new DocBlock();
         $lines = $this->prepare($block);
         $this->startContext('description');
         $buffer = array();
         foreach($lines as $line) {
            if ($line == '') continue;

            if ($line[0]=='@') {
               $docBlock->appendElement(
                  $this->context->getObject($buffer)
               );
               $buffer = array();
               list($name, $payload) = explode(' ', ltrim($line,'@'), 2);
               $this->startContext($name, $payload);
               continue;
            }
            $buffer[] = $line;
         }
         $docBlock->appendElement(
            $this->context->getObject($buffer)
         );
         return $docBlock;
      }

      protected function prepare($block) {
         $block = str_replace(array("\r\n","\r"), "\n", $block);
         $raw = array();
         foreach(explode("\n", $block) as $line) {
            $raw[] = trim($line," *\n\t");
         }
         return $raw;
      }

      protected function startContext($name, $payload = NULL) {
         if (isset($this->map[$name])) {
            $this->context = new $this->map[$name]($name);
         } else {
            $this->context = new GenericContext($name);
         }
         if ($payload !== NULL) {
            $this->context->setPayload($payload);
         }
      }

   }

}