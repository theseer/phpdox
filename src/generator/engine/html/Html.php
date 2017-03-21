<?php
/**
 * Copyright (c) 2010-2017 Arne Blankerts <arne@blankerts.de>
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

    use TheSeer\fDom\fDomDocument;
    use TheSeer\fXSL\fXSLTProcessor;

    use TheSeer\phpDox\FileInfo;
    use TheSeer\phpDox\Generator\AbstractEvent;
    use TheSeer\phpDox\Generator\ClassMethodEvent;
    use TheSeer\phpDox\Generator\ClassStartEvent;
    use TheSeer\phpDox\Generator\InterfaceMethodEvent;
    use TheSeer\phpDox\Generator\InterfaceStartEvent;
    use TheSeer\phpDox\Generator\PHPDoxEndEvent;
    use TheSeer\phpDox\Generator\PHPDoxStartEvent;
    use TheSeer\phpDox\Generator\TokenFileStartEvent;
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
        private $xslInterface;

        /**
         * @var fXSLTProcessor
         */
        private $xslMethod;

        /**
         * @var fXSLTProcessor
         */
        private $xslSource;

        private $templateDir;
        private $resourceDir;
        private $outputDir;
        private $projectNode;
        private $extension;
        private $workDir;
        private $sourceDir;

        private $hasNamespaces = false;
        private $hasInterfaces = false;
        private $hasTraits = false;
        private $hasClasses = false;
        private $hasReports;

        public function __construct(HtmlConfig $config) {
            $this->templateDir = $config->getTemplateDirectory();
            $this->resourceDir = $config->getResourceDirectory();
            $this->outputDir = $config->getOutputDirectory();
            $this->projectNode = $config->getProjectNode();
            $this->extension = $config->getFileExtension();
            $this->workDir = $config->getWorkDirectory();
            $this->sourceDir = $config->getSourceDirectory();
            $this->hasReports = false; // $config->getReports()->count() ?
        }

        public function registerEventHandlers(EventHandlerRegistry $registry) {
            $registry->addHandler('phpdox.start', $this, 'buildStart');
            $registry->addHandler('class.start', $this, 'buildClass');
            $registry->addHandler('trait.start', $this, 'buildTrait');
            $registry->addHandler('interface.start', $this, 'buildInterface');
            $registry->addHandler('class.method', $this, 'buildClassMethod');
            $registry->addHandler('trait.method', $this, 'buildTraitMethod');
            $registry->addHandler('interface.method', $this, 'buildInterfaceMethod');
            $registry->addHandler('token.file.start', $this, 'buildSource');
            $registry->addHandler('phpdox.end', $this, 'buildFinish');
        }

        protected function getXSLTProcessor($template) {
            $xsl = parent::getXSLTProcessor($this->templateDir . '/' . $template);
            $xsl->setParameter('', 'extension', $this->extension);
            $xsl->setParameter('', 'xml', $this->workDir->asFileUri() . '/');

            $xsl->setParameter('', 'hasNamespaces', $this->hasNamespaces ? 'Y' : 'N');
            $xsl->setParameter('', 'hasInterfaces', $this->hasInterfaces ? 'Y' : 'N');
            $xsl->setParameter('', 'hasTraits', $this->hasTraits ? 'Y' : 'N');
            $xsl->setParameter('', 'hasClasses', $this->hasClasses ? 'Y' : 'N');
            $xsl->setParameter('', 'hasReports', $this->hasReports ? 'Y' : 'N');

            if ($this->projectNode->hasAttribute('name')) {
                $xsl->setParameter('', 'project', $this->projectNode->getAttribute('name'));
            }

            return $xsl;
        }

        public function buildStart(PHPDoxStartEvent $event) {
            $this->clearDirectory($this->outputDir);

            $index = $event->getIndex();
            $this->hasNamespaces = $index->hasNamespaces();
            $this->hasInterfaces = $index->hasInterfaces();
            $this->hasTraits = $index->hasTraits();
            $this->hasClasses = $index->hasClasses();

            $this->xslClass = $this->getXSLTProcessor('class.xsl');
            $this->xslClass->setParameter('', 'base', '../');

            $this->xslInterface = $this->getXSLTProcessor('interface.xsl');
            $this->xslInterface->setParameter('', 'base', '../');

            $this->xslMethod = $this->getXSLTProcessor('method.xsl');
            $this->xslMethod->setParameter('', 'base', '../../');

            $this->xslSource = $this->getXSLTProcessor('source.xsl');
        }

        private function renderIndexPages(fDOMDocument $indexDom) {
            $proc = $this->getXSLTProcessor('index.xsl');
            $proc->setParameter('', 'project', $this->projectNode->getAttribute('name'));
            $html = $proc->transformToDoc($indexDom);
            $this->saveDomDocument($html, $this->outputDir . '/index.' . $this->extension);

            $proc = $this->getXSLTProcessor('namespaces.xsl');
            $html = $proc->transformToDoc($indexDom);
            $this->saveDomDocument($html, $this->outputDir . '/namespaces.' . $this->extension);

            $proc = $this->getXSLTProcessor('units.xsl');
            $html = $proc->transformToDoc($indexDom);
            $this->saveDomDocument($html, $this->outputDir . '/classes.' . $this->extension);

            $proc->setParameter('', 'mode', 'interface');
            $proc->setParameter('', 'title', 'Interfaces');
            $html = $proc->transformToDoc($indexDom);
            $this->saveDomDocument($html, $this->outputDir . '/interfaces.' . $this->extension);

            $proc->setParameter('', 'mode', 'trait');
            $proc->setParameter('', 'title', 'Traits');
            $html = $proc->transformToDoc($indexDom);
            $this->saveDomDocument($html, $this->outputDir . '/traits.' . $this->extension);
        }

        private function renderSourceIndexes(fDOMDocument $treeDom) {
            $proc = $this->getXSLTProcessor('directory.xsl');
            $dirList = $treeDom->query('/phpdox:source//phpdox:dir');
            foreach($dirList as $dirNode) {
                $dirNode->setAttributeNS('ctx://engine/html', 'ctx:engine', 'current');

                $parents = $dirNode->query('ancestor-or-self::phpdox:dir');
                $elements = array();
                foreach($parents as $parent) {
                    $elements[] = $parent->getAttribute('name');
                }
                $elements[0] = $this->outputDir . '/source';
                $elements[] = 'index.' . $this->extension;

                $proc->setParameter('', 'base', str_repeat('../', count($elements) - 1));
                $this->saveDomDocument( $proc->transformToDoc($treeDom), join('/', $elements));

                $dirNode->removeAttributeNS('ctx://engine/html', 'engine');
            }
        }

        public function buildFinish(PHPDoxEndEvent $event) {
            $this->renderIndexPages($event->getIndex()->asDom());
            $this->renderSourceIndexes($event->getTree()->asDom());
            $this->copyStatic($this->resourceDir, $this->outputDir, TRUE);
        }

        public function buildClass(ClassStartEvent $event) {
            $this->xslClass->setParameter('', 'type', 'classes');
            $this->xslClass->setParameter('', 'title', 'Classes');
            $html = $this->xslClass->transformToDoc($event->getClass()->asDom());
            $this->saveDomDocument($html, $this->outputDir . '/classes/' .
                $this->classNameToFileName($event->getClass()->getFullName())
            );
        }

        public function buildTrait(TraitStartEvent $event) {
            $this->xslClass->setParameter('', 'type', 'traits');
            $this->xslClass->setParameter('', 'title', 'Traits');
            $html = $this->xslClass->transformToDoc($event->getTrait()->asDom());
            $this->saveDomDocument($html, $this->outputDir . '/traits/' .
                $this->classNameToFileName($event->getTrait()->getFullName())
            );
        }

        public function buildInterface(InterfaceStartEvent $event) {
            $html = $this->xslInterface->transformToDoc($event->getInterface()->asDom());
            $this->saveDomDocument($html, $this->outputDir . '/interfaces/' .
                $this->classNameToFileName($event->getInterface()->getFullName())
            );
        }

        public function buildClassMethod(ClassMethodEvent $event) {
            $this->genericMethodBuild(
                $event->getClass()->asDom(),
                'classes',
                $event->getClass()->getFullName(),
                $event->getMethod()->getName()
            );
        }

        public function buildTraitMethod(TraitMethodEvent $event) {
            $this->genericMethodBuild(
                $event->getTrait()->asDom(),
                'traits',
                $event->getTrait()->getFullName(),
                $event->getMethod()->getName()
            );
        }

        public function buildInterfaceMethod(InterfaceMethodEvent $event) {
            $this->genericMethodBuild(
                $event->getInterface()->asDom(),
                'interfaces',
                $event->getInterface()->getFullName(),
                $event->getMethod()->getName()
            );
        }

        public function buildSource(TokenFileStartEvent $event) {
            $path = $event->getTokenFile()->getRelativeName($this->sourceDir);
            $base = str_repeat('../', count(explode('/', $path)));
            $this->xslSource->setParameter('', 'base', $base);

            $html = $this->xslSource->transformToDoc($event->getTokenFile()->asDom());
            $this->saveDomDocument(
                $html,
                $this->outputDir . '/source/' . $path . '.' . $this->extension,
                FALSE
            );

        }

        private function genericMethodBuild(fDOMDocument $ctx, $target, $unitName, $method) {
            $this->xslMethod->setParameter('', 'methodName', $method);
            $html = $this->xslMethod->transformToDoc($ctx);

            $filename = $this->outputDir . '/' . $target . '/' .
                $this->classNameToFileName($unitName, $method);

            $this->saveDomDocument($html, $filename);
        }

        private function classNameToFileName($class, $method = NULL) {
            $name = str_replace('\\', '_', $class);
            if ($method !== NULL) {
                $name .= '/' . $method;
            }
            return $name . '.' . $this->extension;
        }

    }

}
