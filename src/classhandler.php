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

   /**
    * Stack Handling Inteface
    *
    * @author     Arne Blankerts <arne@blankerts.de>
    * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
    */
   interface stackHandler {

      public function process(\StdClass $context, Array $stack);

   }


   class debugStackHandler implements stackHandler {

      protected $name;

      public function __construct($tokID) {
         $this->name = token_name($tokID);
      }

      public function process(\StdClass $context, Array $stack) {
         /*
         echo "--------- {$this->name} -------\n";
         echo "CONTEXT:";
         var_dump($context->namespace, $context->class, $context->docBlock);
         echo "STACK:";
         var_dump($stack);
         echo "-----------------------------------------------------------\n";
         */
      }

   }

   class functionStackHandler implements stackHandler {

      public function process(\StdClass $context, Array $stack) {
         var_dump($stack);
         $ctx = end($context->nodeStack);
         $ns = $ctx->ownerDocument->createElementNS('http://phpdox.de/xml#','function');
         foreach($stack as $p => $st) {
            if (is_array($st) && $st[1]=='function') {
               $ns->setAttribute('name', $stack[$p+1][1]);
               break;
            }
         }
         $ctx->appendChild($ns);
      }

   }

   class namespaceStackHandler implements stackHandler {

      public function process(\StdClass $context, Array $stack) {
         $context->namespace='';
         array_shift($stack);
         foreach($stack as $tok) {
            $context->namespace .= $tok[1];
         }
         $ctx = end($context->nodeStack);
         $ns = $ctx->ownerDocument->createElementNS('http://phpdox.de/xml#','namespace');
         $ns->setAttribute('name', $context->namespace);
         $ctx->appendChild($ns);
         $context->nodeStack[] = $ns;

      }

   }

   class classStackHandler extends debugStackHandler {

      public function process(\StdClass $context, Array $stack) {
         $pos=0;
         $size = count($stack);
         for($t=0; $t<$size; $t++) {
            $pos++;
            if ($stack[$t][0]==T_CLASS) break;
         }
         $context->class = $stack[$pos][1];

         $ctx = end($context->nodeStack);
         $node = $ctx->ownerDocument->createElementNS('http://phpdox.de/xml#','class');
         $node->setAttribute('name', $context->class);
         $ctx->appendChild($node);
         $context->nodeStack[] = $node;

         //parent::process($context, $stack);
      }

   }

   /**
    * Factory for handler classes
    *
    * @author     Arne Blankerts <arne@blankerts.de>
    * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
    */
   class stackHandlerFactory {

      public function __construct() {
      }

      public function getInstanceFor($tokID) {
         switch ($tokID) {
            case T_NAMESPACE: return new namespaceStackHandler($tokID);
            case T_FUNCTION:  return new functionStackHandler($tokID);
            case T_CLASS:     return new classStackHandler($tokID);
            //case T_CONSTANT_ENCAPSED_STRING:
            default: return new debugStackHandler($tokID);
         }
      }

   }

}