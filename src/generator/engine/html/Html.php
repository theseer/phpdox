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
    use \TheSeer\fXSL\fXSLCallback;

    use \TheSeer\phpDox\Event;

    class Html extends AbstractEngine {

        protected $eventMap = array(
                'phpdox.start' => 'buildStart',
                'class.start' => 'buildClass',
                'interface.start' => 'buildInterface',
                'phpdox.end' => 'buildFinish'
        );

        protected $templateDir;
        protected $outputDir;

        protected $functions;
        protected $classesDom;
        protected $interfacesDom;

        public function __construct(HtmlConfig $config) {
            $this->templateDir = $config->getTemplateDirectory();
            $this->outputDir = $config->getOutputDirectory();
        }

        public function getEvents() {
            return array_keys($this->eventMap);
        }

        public function handle(Event $event) {
            $this->{$this->eventMap[$event->type]}($event);
        }

        protected function buildStart(Event $event) {

            $this->classesDom = $this->getXSLTProcessor($this->templateDir . '/htmlBuilder/list.xsl')->transformToDoc($event->classes);
            $this->interfacesDom = $this->getXSLTProcessor($this->templateDir . '/htmlBuilder/list.xsl')->transformToDoc($event->interfaces);

            $this->functions = new Html\Functions(
                $this->classesDom,
                $this->interfacesDom
            );
            $builder = new fXSLCallback('phpdox:htmlBuilder','hb');
            $builder->setObject($this->functions);

            $index = $this->getXSLTProcessor($this->templateDir . '/htmlBuilder/index.xsl');
            $index->registerCallback($builder);
            $html = $index->transformToDoc($event->classes);

            $this->saveDomDocument($html, $this->outputDir . '/index.xhtml');

            $this->xslClass = $this->getXSLTProcessor($this->templateDir . '/htmlBuilder/class.xsl');
            $this->xslClass->registerCallback($builder);

            $this->xslInterface = $this->getXSLTProcessor($this->templateDir . '/htmlBuilder/interface.xsl');
            $this->xslInterface->registerCallback($builder);

        }

        protected function buildFinish(Event $event) {
            $this->copyStatic($this->templateDir . '/htmlBuilder/static', $this->outputDir, true);
        }

        protected function buildClass(Event $event) {
            $full = $event->class->getAttribute('full');
            $this->xslClass->setParameter('', 'class', $full);
            $html = $this->xslClass->transformToDoc($event->class);
            $this->saveDomDocument($html, $this->outputDir . '/' . $this->functions->classNameToFileName($full, 'xhtml'));
        }

        protected function buildInterface(Event $event) {
            $full = $event->interface->getAttribute('full');
            $this->xslInterface->setParameter('', 'interface', $full);
            $html = $this->xslInterface->transformToDoc($event->interface);
            $this->saveDomDocument($html, $this->outputDir . '/' . $this->functions->classNameToFileName($full, 'xhtml'));
        }

    }

}