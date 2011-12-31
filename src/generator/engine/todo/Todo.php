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

namespace TheSeer\phpDox\Engine {

    use \TheSeer\fDom\fDomDocument;
    use \TheSeer\fDom\fDomElement;
    use \TheSeer\phpDox\Event;
    use \TheSeer\phpDox\BuildConfig;

    class Todo extends AbstractEngine {

        protected $eventMap = array(
            'namespace.start' => 1,
            'class.start' => 1,
            'class.constant' => 1,
            'class.member' => 1,
            'class.method' => 1,
            'interface.start' => 1,
            'interface.constant' => 1,
            'interface.method' => 1,

            'phpdox.end' => 1
        );

        protected $todoList = array();
        protected $outputDir;

        public function __construct(BuildConfig $config) {
            $this->outputDir = $config->getOutputDir();
        }

        public function getEvents() {
            return array_keys($this->eventMap);
        }

        public function handle(Event $event) {
            if ($event->type == 'phpdox.end') {
               return $this->buildFinish();
            }
            switch ($event->type) {
                case 'namespace.start': {
                    $node = $event->namespace;
                    break;
                }
                case 'class.start': {
                    $node = $event->class;
                    break;
                }
                case 'interface.constant':
                case 'class.constant': {
                    $node = $event->constant;
                    break;
                }
                case 'class.member': {
                    $node = $event->member;
                    break;
                }
                case 'interface.method':
                case 'class.method': {
                    $node = $event->method;
                    break;
                }
                case 'interface.start': {
                    $node = $event->interface;
                    break;
                }
            }
            return $this->processItem($node);
        }

        /**
         *
         * @todo Check if this can be implemented in an easier fashon
         */
        protected function buildFinish() {
            $content = "TODO List:\n\n";
            usort($this->todoList, function($a,$b){
                if ($a->namespace != $b->namespace) {
                    return $a->namespace < $b->namespace ? -1 : 1;
                }
                if ($a->class != $b->class) {
                    return $a->class < $b->class ? -1 : 1;
                }
                if ($a->type != $b->type) {
                    return $a->type < $b->type ? -1 : 1;
                }
                return $a->name < $b->name ? -1 : 1;
            });
            foreach($this->todoList as $todo) {
                if (isset($todo->namespace)) {
                    $content .= $todo->namespace . '\\';
                }
                $content .= $todo->class . '::' . $todo->name . ": \n[  ]\t" . $todo->text . "\n\n";
            }
            $this->saveFile($content, $this->outputDir . '/todo.txt');
        }

        protected function processItem(fDOMElement $ctx) {
            $todoNode = $ctx->queryOne('phpdox:docblock/phpdox:todo');
            if ($todoNode) {
                $tmp = new \StdClass;
                $this->todoList[] = $tmp;
                $tmp->text = $todoNode->getAttribute('value');

                if (in_array($ctx->localName, array('method','constructor','destructor'))) {
                    $tmp->type = 'method';
                } else if ($ctx->localName == 'member') {
                    $tmp->type = 'member';
                } else if ($ctx->localName == 'constant') {
                    $tmp->type = 'constant';
                }
                if (isset($tmp->type)) {
                    $tmp->name = $ctx->getAttribute('name');
                    $ctx = $ctx->parentNode;
                }

                if ($ctx->localName == 'class') {
                    $tmp->class = $ctx->getAttribute('name');
                    $ctx = $ctx->parentNode;
                }

                if ($ctx->localName == 'namespace') {
                    $tmp->namespace = $ctx->getAttribute('name');
                }
            }
        }

    }

}