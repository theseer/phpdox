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

        /**
         * @var fXSLTProcessor
         */
        private $xslClass;

        /**
         * @var fXSLTProcessor
         */
        private $xslTrait;

        /**
         * @var fXSLTProcessor
         */
        private $xslInterface;

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

        public function registerEventHandlers(EventHandlerRegistry $registry) {
            $registry->addHandler('phpdox.start',     $this, 'buildStart');
            $registry->addHandler('class.start',      $this, 'buildClass');
//            $registry->addHandler('trait.start',      $this, 'buildTrait');
//            $registry->addHandler('interface.start',  $this, 'buildInterface');
//            $registry->addHandler('class.method',     $this, 'buildClassMethod');
//            $registry->addHandler('trait.method',     $this, 'buildTraitMethod');
//            $registry->addHandler('interface.method', $this, 'buildInterfaceMethod');
            $registry->addHandler('phpdox.end',       $this, 'buildFinish');
        }

        protected function getXSLTProcessor($template) {
            $xsl = parent::getXSLTProcessor($template);
            $xsl->setParameter('', 'extension', $this->extension);
            $xsl->setParameter('', 'xml', '/home/theseer/storage/php/phpdox/build/api/xml/');
            return $xsl;
        }

        public function buildStart(PHPDoxStartEvent $event) {
            $this->functions = new Html\Functions(
                $this->projectNode,
                $event->getIndex()->asDom(),
                $this->extension
            );
            /*
            $builder = new fXSLCallback('phpdox:html', 'phe');
            $builder->setObject($this->functions);
            */

            $this->generateIndex($event);

            $this->xslClass = $this->getXSLTProcessor($this->templateDir . '/class.xsl');
            $this->xslClass->setParameter('', 'base', '../');
            //$this->xslUnit->registerCallback($builder);

            //$this->xslMethod = $this->getXSLTProcessor($this->templateDir . '/method.xsl');
            //$this->xslMethod->registerCallback($builder);

        }

        private function generateIndex(PHPDoxStartEvent $event) {
            $proc = $this->getXSLTProcessor($this->templateDir . '/index.xsl');
            $html = $proc->transformToDoc($event->getIndex()->asDom());
            $this->saveDomDocument($html, $this->outputDir . '/index.' . $this->extension);

            $proc = $this->getXSLTProcessor($this->templateDir . '/namespaces.xsl');
            $html = $proc->transformToDoc($event->getIndex()->asDom());
            $this->saveDomDocument($html, $this->outputDir . '/namespaces.' . $this->extension);

            $proc = $this->getXSLTProcessor($this->templateDir . '/units.xsl');
            $html = $proc->transformToDoc($event->getIndex()->asDom());
            $this->saveDomDocument($html, $this->outputDir . '/classes.' . $this->extension);

            /*
            $proc->setParameter('', 'mode', 'traits');
            $proc->setParameter('', 'title', 'Traits');
            $html = $proc->transformToDoc($event->getIndex()->asDom());
            $this->saveDomDocument($html, $this->outputDir . '/traits.' . $this->extension);
            */
        }

        public function buildFinish(AbstractEvent $event) {
            $this->copyStatic($this->templateDir . '/static', $this->outputDir, TRUE);
        }

        public function buildClass(ClassStartEvent $event) {
            $html = $this->xslClass->transformToDoc($event->getClass()->asDom());
            $this->saveDomDocument($html, $this->outputDir . '/classes/' .
                $this->functions->classNameToFileName($event->getClass()->getFullName())
            );

        }

        public function buildTrait(TraitStartEvent $event) {
            /*
            $this->genericUnitBuild(
                $event->getTrait()->asDom(),
                'traits',
                $event->getTrait()->getFullName()
            );
            */
        }

        public function buildInterface(InterfaceStartEvent $event) {
            /*
            $this->genericUnitBuild(
                $event->getInterface()->asDom(),
                'interfaces',
                $event->getInterface()->getFullName()
            );
            */
        }

        public function buildClassMethod(ClassMethodEvent $event) {
            /*
            $this->genericMethodBuild(
                $event->getClass()->asDom(),
                'classes',
                $event->getClass()->getFullname(),
                $event->getMethod()->getName()
            );
            */
        }

        public function buildTraitMethod(TraitMethodEvent $event) {
            /*
            $this->genericMethodBuild(
                $event->getTrait()->asDom(),
                'traits',
                $event->getTrait()->getFullName(),
                $event->getMethod()->getName()
            );
            */
        }

        public function buildInterfaceMethod(InterfaceMethodEvent $event) {
            /*
            $this->genericMethodBuild(
                $event->getInterface()->asDom(),
                'interfaces',
                $event->getInterface()->getFullName(),
                $event->getMethod()->getName()
            );
            */
        }

        private function genericUnitBuild(fDOMDocument $ctx, $target, $name) {
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