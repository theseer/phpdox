<?php
/**
 * Copyright (c) 2010-2012 Arne Blankerts <arne@blankerts.de>
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

    use \TheSeer\fDom\fDomDocument;
    use \TheSeer\fDom\fDomElement;
    use \TheSeer\fXSL\fXSLCallback;

    use \TheSeer\phpDox\Generator\Event;

    class Html extends AbstractEngine {

        protected $eventMap = array(
                'phpdox.start' => 'buildStart',
                'class.start' => 'buildClass',
                'trait.start' => 'buildTrait',
                'interface.start' => 'buildInterface',
                'phpdox.end' => 'buildFinish'
        );

        protected $xslUnit;
        protected $templateDir;
        protected $outputDir;
        protected $projectNode;
        protected $extension;

        protected $functions;

        public function __construct(HtmlConfig $config) {
            $this->templateDir = $config->getTemplateDirectory();
            $this->outputDir = $config->getOutputDirectory();
            $this->projectNode = $config->getProjectNode();
            $this->extension = $config->getFileExtension();
        }

        public function getEvents() {
            return array_keys($this->eventMap);
        }

        public function handle(Event $event) {
            $this->{$this->eventMap[$event->type]}($event);
        }

        protected function getXSLTProcessor($template) {
            $xsl = parent::getXSLTProcessor($template);
            $xsl->setParameter('', 'extension', $this->extension);
            return $xsl;
        }

        protected function buildStart(Event $event) {
            $this->functions = new Html\Functions(
                $this->projectNode,
                $event->index,
                $this->extension
            );
            $builder = new fXSLCallback('phpdox:html', 'phe');
            $builder->setObject($this->functions);

            $index = $this->getXSLTProcessor($this->templateDir . '/index.xsl');
            $index->registerCallback($builder);
            $html = $index->transformToDoc($event->index);

            $this->saveDomDocument($html, $this->outputDir . '/index.'. $this->extension);

            $this->xslUnit = $this->getXSLTProcessor($this->templateDir . '/unit.xsl');
            $this->xslUnit->registerCallback($builder);
        }

        protected function buildFinish(Event $event) {
            $this->copyStatic($this->templateDir . '/static', $this->outputDir, TRUE);
        }

        protected function buildClass(Event $event) {
            $html = $this->xslUnit->transformToDoc($event->class);
            $this->saveDomDocument($html, $this->outputDir . '/classes/' .
                    $this->functions->classNameToFileName($event->class->getAttribute('full'))
            );
        }

        protected function buildTrait(Event $event) {
            $html = $this->xslUnit->transformToDoc($event->trait);
            $this->saveDomDocument($html, $this->outputDir . '/traitss/' .
                $this->functions->classNameToFileName($event->trait->getAttribute('full'))
            );
        }

        protected function buildInterface(Event $event) {
            $html = $this->xslUnit->transformToDoc($event->interface);
            $this->saveDomDocument($html, $this->outputDir . '/interfaces/' .
                $this->functions->classNameToFileName($event->interface->getAttribute('full'))
            );
        }

    }

}