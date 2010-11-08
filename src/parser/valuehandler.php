<?php
/**
 * Copyright (c) 2010 Arne Blankerts <arne@blankerts.de>
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

namespace TheSeer\phpDox {

   class valueHandler {

      protected $handler;
      protected $ctx;

      protected $mode = 'value';

      public function __construct(stackHandler $handler, processContext $ctx) {
         $this->handler = $handler;
         $this->ctx     = $ctx;
      }

      public function setMode($mode) {
         $this->mode = $mode;
      }

      public function processValue(Array $value, \DomNode $node) {
         $c = count($value);
         if ($c == 0) return;

         $types = array(
            T_CONSTANT_ENCAPSED_STRING => 'string',
            T_DNUMBER => 'float',
            T_LNUMBER => 'int',
            T_STRING  => 'string'
         );

         if ($c == 1) { // Single entry, scalar value
            $node->setAttribute('type', $types[$value[0][0]]);
            $node->setAttribute($this->mode, $value[0][1]);
            return;
         }

         $node->setAttribute('type', 'array');
         $val = $this->handler->createNode('array',$node);

         var_dump($value);

         array_shift($value);
         $stack = array();
         foreach($value as $tok) {
            if ($tok === ',') {
               $this->processArrayElement($stack, $val);
               $stack = array();
               continue;
            }
            $stack[] = $tok;
         }
      }

      protected function processArrayElement(Array $stack, \DomNode $node) {
         $element = $this->handler->createNode('element', $node);
         switch (count($stack)) {
            case 1: {
               $element->setAttribute('value', $stack[0][1]);
               break;
            }
            case 3: {
               $element->setAttribute('key', $stack[0][1]);
               $element->setAttribute('value', $stack[2][1]);
               break;
            }
            default: {
               var_dump($stack);
               //throw new \Exception('Unexpected amount of elements in Stack: '.count($stack));
            }
         }

      }


   }

}