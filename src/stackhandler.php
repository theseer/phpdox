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
    * Factory for handler classes
    *
    * @author     Arne Blankerts <arne@blankerts.de>
    * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
    */
   class stackHandlerFactory {

      protected $dom;

      public function __construct(\DomDocument $dom) {
         $this->dom = $dom;
      }

      public function getInstanceFor($tokID) {
         $candidates=array(
            T_NAMESPACE => 'namespace',
            T_FUNCTION  => 'function',
            T_CLASS     => 'class',
            T_VARIABLE  => 'variable',
            T_CONST     => 'const',
            T_CONSTANT_ENCAPSED_STRING => 'const'
         );
         //$handler='\\TheSeer\\phpDox\\debugStackHandler';
         $handler = __NAMESPACE__ .'\\'. (isset($candidates[$tokID]) ? $candidates[$tokID] : 'debug') . 'StackHandler';
         return new $handler($this->dom, $tokID);
      }

   }

   /**
    * Stack Handling base class
    *
    * @author     Arne Blankerts <arne@blankerts.de>
    * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
    */
   abstract class stackHandler {

      protected $token;
      protected $dom;
      protected $ctxNode;

      public function __construct(\DomDocument $dom, $tok) {
         $this->dom = $dom;
         $this->token = $tok;
      }

      abstract public function process(processContext $context, Array $stack);

      /**
       * Helper to find position of Token on stack
       *
       * @param $tok    A token constant value
       * @param $stack  The Stack Array to search in
       *
       * @return int    Position or null
       */
      protected function findTok($tok, Array $stack) {
         $size = count($stack);
         for($t=0; $t<$size; $t++) {
            if ($stack[$t][0]==$tok) return $t+1;
         }
      }

      protected function createNode($name, \DomElement $ctx = null) {
         if (is_null($ctx)) {
            $ctx = $this->ctxNode;
         }
         $node = $this->dom->createElementNS('http://phpdox.de/xml#', $name);
         $ctx->appendChild($node);
         return $node;
      }

   }

}