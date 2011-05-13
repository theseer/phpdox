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

namespace TheSeer\phpDox {

    use \TheSeer\fDom\fDomDocument;
    use \TheSeer\fDom\fDomElement;

    class HtmlBuilder implements EventHandler {

        protected $xsl;
        protected $generator;

        protected $eventMap = array(
            'phpdox.start' => 'buildStart',
            'class.start' => 'buildClass',
            'phpdox.end' => 'buildFinish'
        );

        public function setUp(Generator $generator) {
            $this->generator = $generator;
            foreach(array_keys($this->eventMap) as $event) {
                $generator->registerHandler($event, $this);
            }
            $this->xsl = $generator->getXSLTProcessor('htmlBuilder/class.xsl');
        }

        public function handle($event) {
            if (!isset($this->eventMap[$event])) {
                throw new HtmlBuilderException("Don't know how to handle event '$event'", HtmlBuilderException::UnkownEvent);
            }
            $payload = func_get_args();
            array_shift($payload);
            call_user_func_array(array($this, $this->eventMap[$event]), $payload);
        }

        protected function buildStart(fDOMDocument $namespace, fDOMDocument $classes, fDOMDocument $interfaces) {
            $html = $this->generator->getXSLTProcessor('htmlBuilder/list.xsl')->transformToDoc($classes);
            $this->generator->saveDomDocument($html, 'list.xhtml');
        }

        protected function buildFinish() {
            $this->generator->copyStatic('htmlBuilder/static', true);
        }

        protected function buildClass(fDOMElement $classNode) {
            $full = $classNode->getAttribute('full');
            $this->xsl->setParameter('', 'class', $full);
            $html = $this->xsl->transformToDoc($classNode);
            $this->generator->saveDomDocument($html, 'classes/'. $this->classNameToFileName($full, 'xhtml'));
        }

        protected function classNameToFileName($class, $ext = 'xml') {
            return str_replace('\\', '_', $class) . '.' . $ext;
        }

    }

    class HtmlBuilderException extends \Exception {
        const UnkownEvent = 1;
    }

}