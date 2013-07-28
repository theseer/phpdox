<?php
/**
 * Copyright (c) 2010-2013 Arne Blankerts <arne@blankerts.de>
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

namespace TheSeer\phpDox\Generator\Engine {

    use \TheSeer\fDom\fDomElement;
    use \TheSeer\phpDox\Generator\AbstractEvent;
    use \TheSeer\phpDox\BuildConfig;
    use TheSeer\phpDox\Generator\ClassStartEvent;
    use TheSeer\phpDox\Generator\InterfaceStartEvent;
    use TheSeer\phpDox\Generator\TraitStartEvent;

    class Graph extends AbstractEngine {

        private $outputDir;
        private $dotCommand;
        private $content = array();

        private $eventMap = array(
            'class.start' =>  1,
            'interface.start' => 1,
            'trait.start' => 1,
            'phpdox.end' => 1
        );

        public function __construct(GraphConfig $config) {
            $this->outputDir = $config->getOutputDirectory();
            $this->dotCommand = $config->getDotCommand();
        }

        public function getEvents() {
            return array_keys($this->eventMap);
        }

        public function handle(AbstractEvent $event) {
            if ($event->getType() == 'phpdox.end') {
                $content = "digraph phpdox {\n".join("\n", $this->content)."\n}";
                $this->saveFile($content, $this->outputDir . '/graph.dot');
                $wd = $this->outputDir;
                exec("{$this->dotCommand} $wd/graph.dot 2>&1 >/dev/null");
                return;
            }
            if ($event instanceof ClassStartEvent) {
                $this->renderNode($event->getClass(), 'box');
            } elseif ($event instanceof InterfaceStartEvent) {
                $this->renderNode($event->getInterface(), 'oval');
            } else {
                /** @var $event TraitStartEvent */
                $this->renderNode($event->getTrait());
            }
        }

        protected function renderNode(fDOMElement $node, $shape = 'box') {
            $class = '"' . addSlashes($node->getAttribute('full')) . '"';
            $this->addContent("$class [shape=$shape]");
            if ($extendsNode = $node->queryOne('phpdox:extends')) {
                $extends = '"' . addSlashes($extendsNode->getAttribute('full')) . '"';
                $this->addContent("$extends [shape=box]");
                $this->addContent("$extends -> $class");
            }
            $implementNodes = $node->query('phpdox:implements');
            foreach($implementNodes as $interfaceNode) {
                $interface = '"' . addSlashes($interfaceNode->getAttribute('full')) . '"';
                $this->addContent("$interface");
                $this->addContent("$interface -> $class");
            }
        }

        protected function addContent($line) {
            if (!in_array($line, $this->content)) {
                $this->content[] = $line;
            }
        }
    }

}