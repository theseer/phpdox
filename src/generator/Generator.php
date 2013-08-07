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
namespace TheSeer\phpDox\Generator {

    use \TheSeer\fXSL\fXSLTProcessor;
    use \TheSeer\fDom\fDomElement;
    use \TheSeer\fDOM\fDOMDocument;

    use \TheSeer\phpDox\Generator\Engine\EngineInterface;
    use \TheSeer\phpDox\Generator\Enricher\EnricherInterface;
    use \TheSeer\phpDox\ProgressLogger;
    use \TheSeer\phpDox\Project\Project;

    class Generator {

        /**
         * @var EventFactory
         */
        private $factory;

        /**
         * @var \TheSeer\phpDox\ProgressLogger
         */
        private $logger;

        /**
         * @var array
         */
        private $engines = array();

        /**
         * @var array
         */
        private $enrichers = array();

        /**
         * @var bool
         */
        private $publicOnly;

        /**
         * @var string
         */
        private $xmlDir;

        /**
         * @var Project
         */
        private $project;

        /**
         * Map of events with engines
         *
         * @var array
         */
        private $events = array(
            'phpdox.raw' => array(),
            'phpdox.start' => array(),
            'phpdox.end' => array(),

            'phpdox.namespaces.start' => array(),
            'phpdox.namespaces.end' => array(),

            'phpdox.classes.start' => array(),
            'phpdox.classes.end' => array(),
            'phpdox.traits.start' => array(),
            'phpdox.traits.end' => array(),
            'phpdox.interfaces.start' => array(),
            'phpdox.interfaces.end' => array(),

            'namespace.start' => array(),
            'namespace.classes.start' => array(),
            'namespace.classes.end' => array(),
            'namespace.traits.start' => array(),
            'namespace.traits.end' => array(),
            'namespace.interfaces.start' => array(),
            'namespace.interfaces.end' => array(),
            'namespace.end' => array(),

            'class.start' => array(),
            'class.constant' => array(),
            'class.member' => array(),
            'class.method' => array(),
            'class.end' => array(),

            'trait.start' => array(),
            'trait.constant' => array(),
            'trait.member' => array(),
            'trait.method' => array(),
            'trait.end' => array(),

            'interface.start' => array(),
            'interface.constant' => array(),
            'interface.method' => array(),
            'interface.end' => array()
        );

        private $enricherEvents = array(
            'class.start',
            'trait.start',
            'interface.start'
        );

        /**
         * @param EventFactory   $factory
         * @param ProgressLogger $logger
         */
        public function __construct(EventFactory $factory, ProgressLogger $logger) {
            $this->factory = $factory;
            $this->logger = $logger;
        }

        /**
         * @param EngineInterface $engine
         *
         * @throws
         * @throws GeneratorException
         */
        public function addEngine(EngineInterface $engine) {
            $this->engines[] = $engine;
            foreach($engine->getEvents() as $event) {
                if (!array_key_exists($event, $this->events)) {
                    throw new GeneratorException("'$event' is unknown", GeneratorException::UnknownEvent);
                }
                $hash = spl_object_hash($engine);
                if (isset($this->events[$event][$hash])) {
                    throw new GeneratorException("Engine instance already registered for event '$event'", GeneratorException::AlreadyRegistered);
                }
                $this->events[$event][$hash] = $engine;
            }
        }

        public function addEnricher(EnricherInterface $enricher) {
            $this->enrichers[] = $enricher;
        }

        /**
         * @param Project $project
         * @param bool    $publicOnly
         */
        public function run(Project $project, $publicOnly = FALSE) {
            $this->xmlDir     = $project->getXmlDir();
            $this->publicOnly = $publicOnly;
            $this->project    = $project;

            $this->handleEvent(new PHPDoxStartEvent($project->getIndex(), $project->getSourceTree()));
            if ($this->project->hasNamespaces()) {
                $this->processWithNamespace();
            } else {
                $this->processGlobalOnly();
            }
            $this->handleEvent(new PHPDoxEndEvent($project->getIndex(), $project->getSourceTree()));
            $this->logger->completed();

            $this->logger->log("Triggering raw engines\n");
            $this->handleEvent(new PHPDoxRawEvent(), FALSE);
        }

        /**
         * @param AbstractEvent $event
         * @param bool          $progress
         */
        protected function handleEvent(AbstractEvent $event, $progress = TRUE) {
            if (in_array($event->getType(), $this->enricherEvents)) {
                foreach($this->enrichers as $enricher) {
                    $enricher->enrich($event);
                }
            }
            foreach($this->events[$event->getType()] as $engine) {
                $engine->handle($event);
            }
            if ($progress) {
                $this->logger->progress('processed');
            }
    }

        /**
         *
         */
        protected function processGlobalOnly() {
            $classes = $this->project->getClasses();
            $this->handleEvent(new PHPDoxClassesStartEvent($classes));
            foreach($classes as $class) {
                $this->processClass($class);
            }
            $this->handleEvent(new PHPDoxClassesEndEvent($classes));

            $traits = $this->project->getTraits();
            $this->handleEvent(new PHPDoxTraitsStartEvent($traits));
            foreach($traits as $trait) {
                $this->processTrait($trait);
            }
            $this->handleEvent(new PHPDoxTraitsEndEvent($traits));

            $interfaces = $this->project->getInterfaces();
            $this->handleEvent(new PHPDoxInterfacesStartEvent($interfaces));
            foreach($interfaces as $interface) {
                $this->processInterface($interface);
            }
            $this->handleEvent(new PHPDoxInterfacesEndEvent($interfaces));
        }

        /**
         *
         */
        protected function processWithNamespace() {
            $namespaces = $this->project->getNamespaces();
            $this->handleEvent(new PHPDoxNamespacesStartEvent($namespaces));

            foreach($namespaces as $namespace) {
                $this->handleEvent(new NamespaceStartEvent($namespace));

                $classes = $this->project->getClasses($namespace->getAttribute('name'));
                $this->handleEvent(new NamespaceClassesStartEvent($classes, $namespace));
                foreach($classes as $class) {
                    $this->processClass($class);
                }
                $this->handleEvent(new NamespaceClassesEndEvent($classes, $namespace));

                $traits = $this->project->getTraits($namespace->getAttribute('name'));
                $this->handleEvent(new NamespaceTraitsStartEvent($traits, $namespace));
                foreach($traits as $trait) {
                    $this->processTrait($trait);
                }
                $this->handleEvent(new NamespaceTraitsEndEvent($traits, $namespace));

                $interfaces = $this->project->getInterfaces($namespace->getAttribute('name'));
                $this->handleEvent(new NamespaceInterfacesStartEvent($interfaces, $namespace));
                foreach($interfaces as $interface) {
                    $this->processInterface($interface);
                }
                $this->handleEvent(new NamespaceInterfacesEndEvent($interfaces, $namespace));

                $this->handleEvent(new NamespaceEndEvent($namespace));
            }
            $this->handleEvent(new PHPDoxNamespacesEndEvent($namespaces));
        }

        /**
         * @param fDomElement $class
         */
        protected function processClass(fDOMElement $class) {
            $classDom = new fDomDocument();
            $classDom->load($this->xmlDir . '/' . $class->getAttribute('xml'));
            $classDom->registerNamespace('phpdox', 'http://xml.phpdox.de/src#');

            foreach($classDom->query('//phpdox:class') as $classNode) {
                $this->handleEvent(new ClassStartEvent($classNode));

                foreach($classNode->query('phpdox:constant') as $constant) {
                    $this->handleEvent(new ClassConstantEvent($constant, $classNode));
                }

                foreach($classNode->query('phpdox:member') as $member) {
                    if ($this->publicOnly && ($member->getAttribute('visibility')!='public')) {
                        continue;
                    }
                    $this->handleEvent(new ClassMemberEvent($member, $classNode));
                }

                foreach($classNode->query('phpdox:method') as $method) {
                    if ($this->publicOnly && ($method->getAttribute('visibility')!='public')) {
                        continue;
                    }
                    $this->handleEvent(new ClassMethodEvent($method, $classNode));
                }
                $this->handleEvent(new ClassEndEvent($classNode));
            }
        }

        /**
         * @param fDomElement $trait
         */
        protected function processTrait(fDOMElement $trait) {
            $traitDom = new fDomDocument();
            $traitDom->load($this->xmlDir . '/' . $trait->getAttribute('xml'));
            $traitDom->registerNamespace('phpdox', 'http://xml.phpdox.de/src#');

            foreach($traitDom->query('//phpdox:trait') as $traitNode) {
                $this->handleEvent(new TraitStartEvent($traitNode));

                foreach($traitNode->query('phpdox:constant') as $constant) {
                    $this->handleEvent(new TraitConstantEvent($constant, $traitNode));
                }

                foreach($traitNode->query('phpdox:member') as $member) {
                    if ($this->publicOnly && ($member->getAttribute('visibility')!='public')) {
                        continue;
                    }
                    $this->handleEvent(new TraitMemberEvent($member, $traitNode));
                }

                foreach($traitNode->query('phpdox:method') as $method) {
                    if ($this->publicOnly && ($method->getAttribute('visibility')!='public')) {
                        continue;
                    }
                    $this->handleEvent(new TraitMethodEvent($method, $traitNode));
                }
                $this->handleEvent(new TraitEndEvent($traitNode));
            }
        }

        /**
         * @param fDomElement $interface
         */
        protected function processInterface(fDOMElement $interface) {
            $interfaceDom = new fDomDocument();
            $interfaceDom->load($this->xmlDir . '/' . $interface->getAttribute('xml'));
            $interfaceDom->registerNamespace('phpdox', 'http://xml.phpdox.de/src#');

            foreach($interfaceDom->query('//phpdox:interface') as $interfaceNode) {
                $this->handleEvent(new InterfaceStartEvent($interfaceNode));

                foreach($interfaceNode->query('phpdox:constant') as $constant) {
                    $this->handleEvent(new InterfaceConstantEvent($constant, $interfaceNode));
                }

                foreach($interfaceNode->query('phpdox:method') as $method) {
                    $this->handleEvent(new InterfaceMethodEvent($method, $interfaceNode));
                }

                $this->handleEvent(new InterfaceEndEvent($interfaceNode));
            }
        }

    }

    class GeneratorException extends \Exception {
        const UnknownEvent = 1;
        const AlreadyRegistered = 2;
    }

}
