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

    use \TheSeer\fDom\fDomDocument;
    use \TheSeer\fDom\fDomElement;
    use \TheSeer\fXSL\fXSLCallback;

    use TheSeer\fXSL\fXSLTProcessor;
    use \TheSeer\phpDox\Generator\AbstractEvent;
    use TheSeer\phpDox\Generator\ClassMethodEvent;
    use TheSeer\phpDox\Generator\ClassStartEvent;
    use TheSeer\phpDox\Generator\InterfaceMethodEvent;
    use TheSeer\phpDox\Generator\InterfaceStartEvent;
    use TheSeer\phpDox\Generator\PHPDoxStartEvent;
    use TheSeer\phpDox\Generator\TraitMethodEvent;
    use TheSeer\phpDox\Generator\TraitStartEvent;

    class Html extends AbstractEngine {

        private $eventMap = array(
            'phpdox.start' => 'buildStart',
            'class.start' => 'buildClass',
            'trait.start' => 'buildTrait',
            'interface.start' => 'buildInterface',
            'class.method' => 'buildClassMethod',
            'trait.method' => 'buildTraitMethod',
            'interface.method' => 'buildInterfaceMethod',
            'phpdox.end' => 'buildFinish'
        );

        /**
         * @var fXSLTProcessor
         */
        private $xslUnit;

        /**
         * @var fXSLTProcessor
         */
        private $xslMethod;

        private $templateDir;
        private $outputDir;
        private $projectNode;
        private $extension;

        private $functions;

        public function __construct(HtmlConfig $config) {
            $this->templateDir = $config->getTemplateDirectory();
            $this->outputDir = $config->getOutputDirectory();
            $this->projectNode = $config->getProjectNode();
            $this->extension = $config->getFileExtension();
        }

        public function getEvents() {
            return array_keys($this->eventMap);
        }

        public function handle(AbstractEvent $event) {
            $this->{$this->eventMap[$event->getType()]}($event);
        }

        protected function getXSLTProcessor($template) {
            $xsl = parent::getXSLTProcessor($template);
            $xsl->setParameter('', 'extension', $this->extension);
            return $xsl;
        }

        private function buildStart(PHPDoxStartEvent $event) {
            $this->functions = new Html\Functions(
                $this->projectNode,
                $event->getIndex()->asDom(),
                $this->extension
            );
            $builder = new fXSLCallback('phpdox:html', 'phe');
            $builder->setObject($this->functions);

            $index = $this->getXSLTProcessor($this->templateDir . '/index.xsl');
            $index->registerCallback($builder);
            $html = $index->transformToDoc($event->getIndex()->asDom());

            $this->saveDomDocument($html, $this->outputDir . '/index.'. $this->extension);

            $this->xslUnit = $this->getXSLTProcessor($this->templateDir . '/unit.xsl');
            $this->xslUnit->registerCallback($builder);

            $this->xslMethod = $this->getXSLTProcessor($this->templateDir . '/method.xsl');
            $this->xslMethod->registerCallback($builder);

        }

        private function buildFinish(AbstractEvent $event) {
            $this->copyStatic($this->templateDir . '/static', $this->outputDir, TRUE);
        }

        private function buildClass(ClassStartEvent $event) {
            $this->genericUnitBuild(
                $event->getClass()->asDom(),
                'classes',
                $event->getClass()->getFullName()
            );
        }

        private function buildTrait(TraitStartEvent $event) {
            $this->genericUnitBuild(
                $event->getTrait()->asDom(),
                'traits',
                $event->getTrait()->getFullName()
            );
        }

        private function buildInterface(InterfaceStartEvent $event) {
            $this->genericUnitBuild(
                $event->getInterface()->asDom(),
                'interfaces',
                $event->getInterface()->getFullName()
            );
        }

        private function genericUnitBuild(fDOMDocument $ctx, $target, $name) {
            $html = $this->xslUnit->transformToDoc($ctx);
            $this->saveDomDocument($html, $this->outputDir . '/' . $target . '/' .
                $this->functions->classNameToFileName($name)
            );
        }

        private function buildClassMethod(ClassMethodEvent $event) {
            $this->genericMethodBuild(
                $event->getClass()->asDom(),
                'classes',
                $event->getClass()->getFullname(),
                $event->getMethod()->getName()
            );
        }

        private function buildTraitMethod(TraitMethodEvent $event) {
            $this->genericMethodBuild(
                $event->getTrait()->asDom(),
                'traits',
                $event->getTrait()->getFullName(),
                $event->getMethod()->getName()
            );
        }

        private function buildInterfaceMethod(InterfaceMethodEvent $event) {
            $this->genericMethodBuild(
                $event->getInterface()->asDom(),
                'interfaces',
                $event->getInterface()->getFullName(),
                $event->getMethod()->getName()
            );
        }

        private function genericMethodBuild(fDOMDocument $ctx, $target, $unitName, $method) {
            $this->xslMethod->setParameter('', 'method', $method);
            $html = $this->xslMethod->transformToDoc($ctx);
            $this->saveDomDocument($html, $this->outputDir . '/' . $target . '/' .
                $this->functions->classNameToFileName($unitName, $method)
            );
        }

    }

}