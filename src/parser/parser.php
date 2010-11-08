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
    * Namespace aware parser to process php source files for docs
    *
    * @author     Arne Blankerts <arne@blankerts.de>
    * @copyright  Arne Blankerts <arne@blankerts.de>, All rights reserved.
    */
   class parser {

      protected $tokenStack = array();
      protected $stack = false;
      protected $handler = null;

      protected $context = null;

      protected $stackHandlerFactory;

      public function __construct(stackHandlerFactory $factory, \DomElement $node) {
         $this->stackHandlerFactory = $factory;
         $this->context = new processContext($node);
      }

      public function parseFile($filename) {
         $this->context->setFileName($filename);
         $this->context->getStackNode()->setAttribute('file', realpath($filename));
         $tokens = token_get_all(file_get_contents($filename));
         $bracketCount = 0;
         $wait = false;
         $waitBracketCount = array(
            'namespace' => 0,
            'class' => 0,
            'function' => 0
         );
         $nsStyle = false;

         foreach($tokens as $tok) {
            //var_dump($tok);

            if (is_array($tok)) {
               switch ($tok[0]) {
                  case T_WHITESPACE: {
                     continue 2;
                  }
                  case T_NAMESPACE: {
                     $this->handler = $this->stackHandlerFactory->getInstanceFor(T_NAMESPACE);
                     $this->stack = true;
                     continue;
                  }

                  case T_DOC_COMMENT: {
                     // TODO: Run this through docblock parser and add resulting object
                     $this->context->setDocBlock($tok);
                     continue;
                  }

                  case T_VARIABLE: {
                     if (!is_null($this->handler) || $wait) break;
                     $this->handler = $this->stackHandlerFactory->getInstanceFor(T_VARIABLE);
                     break;
                  }

                  case T_CONST:
                  case T_CONSTANT_ENCAPSED_STRING:
                  case T_FUNCTION: {
                     if ($wait) break;
                  }

                  case T_INTERFACE:
                  case T_CLASS: {
                     if (is_null($this->handler)) {
                        $this->handler = $this->stackHandlerFactory->getInstanceFor($tok[0]);
                     }
                     $this->stack = true;
                     break;
                  }

                  case T_PUBLIC:
                  case T_PROTECTED:
                  case T_PRIVATE:
                  case T_ABSTRACT:
                  case T_FINAL: {
                     $this->stack = true;
                  }

               }
               if ($this->stack) {
                  $this->tokenStack[] = $tok;
               }
            } else {
               switch ($tok) {
                  case '}': {
                     if ($waitBracketCount['function']==$bracketCount) {
                        $wait = false;
                        $waitBracketCount['function']=0;
                     } else if ($waitBracketCount['class']==$bracketCount) {
                        $this->context->setClass('');
                        $this->context->decrementStack();
                        $waitBracketCount['class']=0;
                     } else if ($waitBracketCount['namespace']==$bracketCount) {
                        $this->context->setNamespace('');
                        $this->context->decrementStack();
                        $waitBracketCount['namespace']=0;
                     }
                     $bracketCount--;
                     break;
                  }
                  case '{': {
                     $bracketCount++;
                     if ($this->handler instanceof namespaceStackHandler) {
                        $nsStyle = true;
                        $waitBracketCount['namespace'] = $bracketCount;
                     } else if ($this->handler instanceof classStackHandler || $this->handler instanceof interfaceStackHandler) {
                        $waitBracketCount['class'] = $bracketCount;
                     } else if ($this->handler instanceof functionStackHandler) {
                        $waitBracketCount['function'] = $bracketCount;
                        $wait = true;
                     }
                     // no break!
                  }
                  case ';': {
                     if ($this->handler instanceof stackHandler) {
                        $this->handler->process($this->context, $this->tokenStack);
                        $this->tokenStack = array();
                        $this->handler = null;
                        $this->stack = false;
                     }
                     break;
                  }
                  case ',': {
                     $this->tokenStack[] = $tok;
                     break;
                  }
                  case '(':
                  case ')': {
                     if ($this->handler instanceof functionStackHandler) {
                        $this->tokenStack[] = $tok;
                     }
                     break;
                  }
               }
            }
         }
      }
   }

}
