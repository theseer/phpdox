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
namespace TheSeer\phpDox\Generator {

    use \TheSeer\fXSL\fXSLTProcessor;
    use \TheSeer\fDom\fDomElement;
    use \TheSeer\fDOM\fDOMDocument;

    use \TheSeer\phpDox\Generator\Engine\EngineInterface;
    use \TheSeer\phpDox\ProgressLogger;
    use \TheSeer\phpDox\Project\Project;

    class Generator {

        protected $factory;
        protected $logger;

        protected $engines = array();

        protected $publicOnly;
        protected $xmlDir;

        /**
         * @var Project
         */
        protected $project;

        /**
         * Map of events with engines
         *
         * @var array
         */
        protected $events = array(
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

        public function __construct(EventFactory $factory, ProgressLogger $logger) {
            $this->factory = $factory;
            $this->logger = $logger;
        }

        public function addEngine(EngineInterface $engine) {
            $this->engines[] = $engine;
            foreach($engine->getEvents() as $event) {
                if (!array_key_exists($event, $this->events)) {
                    throw new GeneratorException("'$event' is unknown", GeneratorException::UnknownEvent);
                }
                $hash = spl_object_hash($engine);
                if (isset($this->events[$event][$hash])) {
                    throw GeneratorException("Engine instance already registered for event '$event'", GeneratorException::AlreadyRegistered);
                }
                $this->events[$event][$hash] = $engine;
            }
        }

        public function run(Project $project, $publicOnly = FALSE) {
            $this->xmlDir     = $project->getXmlDir();
            $this->publicOnly = $publicOnly;
            $this->project    = $project;

            $this->triggerEvent('phpdox.start', $project->getIndex(), $project->getSourceTree());
            if ($this->project->hasNamespaces()) {
                $this->processWithNamespace();
            } else {
                $this->processGlobalOnly();
            }
            $this->triggerEvent('phpdox.end', $project->getIndex(), $project->getSourceTree());
            $this->logger->completed();

            $this->logger->log("Triggering raw engines\n");
            $this->triggerEvent('phpdox.raw', FALSE);

        }

        protected function triggerEvent($eventName, $progress = TRUE) {
            $payload = array_slice(func_get_args(), 1);
            $event = $this->factory->getInstanceFor($eventName, $payload);
            foreach($this->events[$eventName] as $engine) {
                $engine->handle($event);
            }
            if ($progress) {
                $this->logger->progress('processed');
            }
        }

        protected function processGlobalOnly() {
            $classes = $this->project->getClasses();
            $this->triggerEvent('phpdox.classes.start', $classes);
            foreach($classes as $class) {
                $this->processClass($class);
            }
            $this->triggerEvent('phpdox.classes.end', $classes);

            $traits = $this->project->getTraits();
            $this->triggerEvent('phpdox.traits.start', $traits);
            foreach($traits as $trait) {
                $this->processTrait($trait);
            }
            $this->triggerEvent('phpdox.traits.end', $traits);

            $interfaces = $this->project->getInterfaces();
            $this->triggerEvent('phpdox.interfaces.start', $interfaces);
            foreach($interfaces as $interface) {
                $this->processInterface($interface);
            }
            $this->triggerEvent('phpdox.interfaces.end', $interfaces);
        }

        protected function processWithNamespace() {
            $namespaces = $this->project->getNamespaces();
            $this->triggerEvent('phpdox.namespaces.start', $namespaces);

            foreach($namespaces as $namespace) {
                $this->triggerEvent('namespace.start', $namespace);

                $classes = $this->project->getClasses($namespace->getAttribute('name'));
                $this->triggerEvent('namespace.classes.start', $classes, $namespace);
                foreach($classes as $class) {
                    $this->processClass($class);
                }
                $this->triggerEvent('namespace.classes.end', $classes, $namespace);

                $traits = $this->project->getTraits($namespace->getAttribute('name'));
                $this->triggerEvent('namespace.traits.start', $traits, $namespace);
                foreach($traits as $trait) {
                    $this->processTrait($trait);
                }
                $this->triggerEvent('namespace.traits.end', $traits, $namespace);

                $interfaces = $this->project->getInterfaces($namespace->getAttribute('name'));
                $this->triggerEvent('namespace.interfaces.start', $interfaces, $namespace);
                foreach($interfaces as $interface) {
                    $this->processInterface($interface);
                }
                $this->triggerEvent('namespace.interfaces.end', $interfaces, $namespace);

                $this->triggerEvent('namespace.end', $namespace);
            }
            $this->triggerEvent('phpdox.namespaces.end', $namespaces);
        }

        protected function processClass(fDOMElement $class) {
            $classDom = new fDomDocument();
            $classDom->load($this->xmlDir . '/' . $class->getAttribute('xml'));
            $classDom->registerNamespace('phpdox', 'http://xml.phpdox.de/src#');

            foreach($classDom->query('//phpdox:class') as $classNode) {
                $this->triggerEvent('class.start', $classNode);

                foreach($classNode->query('phpdox:constant') as $constant) {
                    $this->triggerEvent('class.constant', $constant, $classNode);
                }

                foreach($classNode->query('phpdox:member') as $member) {
                    if ($this->publicOnly && ($member->getAttribute('visibility')!='public')) {
                        continue;
                    }
                    $this->triggerEvent('class.member', $member, $classNode);
                }

                foreach($classNode->query('phpdox:method') as $method) {
                    if ($this->publicOnly && ($method->getAttribute('visibility')!='public')) {
                        continue;
                    }
                    $this->triggerEvent('class.method', $method, $classNode);
                }
                $this->triggerEvent('class.end', $classNode);
            }
        }

        protected function processTrait(fDOMElement $trait) {
            $traitDom = new fDomDocument();
            $traitDom->load($this->xmlDir . '/' . $trait->getAttribute('xml'));
            $traitDom->registerNamespace('phpdox', 'http://xml.phpdox.de/src#');

            foreach($traitDom->query('//phpdox:trait') as $traitNode) {
                $this->triggerEvent('trait.start', $traitNode);

                foreach($traitNode->query('phpdox:constant') as $constant) {
                    $this->triggerEvent('trait.constant', $constant, $traitNode);
                }

                foreach($traitNode->query('phpdox:member') as $member) {
                    if ($this->publicOnly && ($member->getAttribute('visibility')!='public')) {
                        continue;
                    }
                    $this->triggerEvent('trait.member', $member, $traitNode);
                }

                foreach($traitNode->query('phpdox:method') as $method) {
                    if ($this->publicOnly && ($method->getAttribute('visibility')!='public')) {
                        continue;
                    }
                    $this->triggerEvent('trait.method', $method, $traitNode);
                }
                $this->triggerEvent('trait.end', $traitNode);
            }
        }
        
        protected function processInterface(fDOMElement $interface) {
            $interfaceDom = new fDomDocument();
            $interfaceDom->load($this->xmlDir . '/' . $interface->getAttribute('xml'));
            $interfaceDom->registerNamespace('phpdox', 'http://xml.phpdox.de/src#');

            foreach($interfaceDom->query('//phpdox:interface') as $interfaceNode) {
                $this->triggerEvent('interface.start', $interfaceNode);

                foreach($interfaceNode->query('phpdox:constant') as $constant) {
                    $this->triggerEvent('interface.constant', $constant, $interfaceNode);
                }

                foreach($interfaceNode->query('phpdox:method') as $method) {
                    $this->triggerEvent('interface.method', $method, $interfaceNode);
                }

                $this->triggerEvent('interface.end', $interfaceNode);
            }
        }

    }

    class GeneratorException extends \Exception {
        const UnknownEvent = 1;
        const AlreadyRegistered = 2;
    }

}
