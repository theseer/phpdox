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
    use \TheSeer\fXSL\fXSLCallback;

    use \TheSeer\fDom\fDomDocument;
    use \TheSeer\fDom\fDomElement;

    use \TheSeer\phpDox\Generator\Engine\EngineInterface;
    use \TheSeer\phpDox\ProgressLogger;
    use \TheSeer\phpDox\Container;

    class Generator {

        protected $factory;
        protected $logger;

        protected $engines = array();

        protected $publicOnly;
        protected $xmlDir;

        protected $namespaces;
        protected $classes;
        protected $interfaces;

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
            'phpdox.interfaces.start' => array(),
            'phpdox.interfaces.end' => array(),

            'namespace.start' => array(),
            'namespace.classes.start' => array(),
            'namespace.classes.end' => array(),
            'namespace.interfaces.start' => array(),
            'namespace.interfaces.end' => array(),
            'namespace.end' => array(),

            'class.start' => array(),
            'class.constant' => array(),
            'class.member' => array(),
            'class.method' => array(),
            'class.end' => array(),

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

        public function run(Container $container, $publicOnly = false) {
            $this->xmlDir     = $container->getWorkDir();
            $this->publicOnly = $publicOnly;

            $this->namespaces = $container->getDocument('namespaces');
            $this->classes    = $container->getDocument('classes');
            $this->interfaces = $container->getDocument('interfaces');

            $this->triggerEvent('phpdox.start', $this->namespaces, $this->classes, $this->interfaces);
            if ($this->namespaces->documentElement->hasChildNodes()) {
                $this->processWithNamespace();
            } else {
                $this->processGlobalOnly();
            }
            $this->triggerEvent('phpdox.end', $this->namespaces, $this->classes, $this->interfaces);
            $this->logger->completed();

            $this->logger->log("Triggering raw engines\n");
            $this->triggerEvent('phpdox.raw', false);

        }

        protected function triggerEvent($eventName, $progress = true) {
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
            $this->triggerEvent('phpdox.classes.start', $this->classes);
            foreach($this->classes->query('//phpdox:class') as $class) {
                $this->processClass($class);
            }
            $this->triggerEvent('phpdox.classes.end', $this->classes);
            $this->triggerEvent('phpdox.interfaces.start', $this->interfaces);
            foreach($this->interfaces->query('//phpdox:interface') as $interface) {
                $this->processInterface($interface);
            }
            $this->triggerEvent('phpdox.interfaces.end', $this->interfaces);
        }

        protected function processWithNamespace() {
            $this->triggerEvent('phpdox.namespaces.start', $this->namespaces);

            foreach($this->namespaces->query('//phpdox:namespace') as $namespace) {
                $this->triggerEvent('namespace.start', $namespace);
                $this->triggerEvent('namespace.classes.start', $this->classes, $namespace);

                $xpath = sprintf('//phpdox:namespace[@name="%s"]/phpdox:class', $namespace->getAttribute('name'));
                foreach($this->classes->query($xpath) as $class) {
                    $this->processClass($class);
                }

                $this->triggerEvent('namespace.classes.end', $this->classes, $namespace);
                $this->triggerEvent('namespace.interfaces.start', $this->interfaces, $namespace);

                $xpath = sprintf('//phpdox:namespace[@name="%s"]/phpdox:interface', $namespace->getAttribute('name'));
                foreach($this->interfaces->query($xpath) as $interface) {
                    $this->processInterface($interface);
                }

                $this->triggerEvent('namespace.interfaces.end', $this->interfaces, $namespace);
                $this->triggerEvent('namespace.end', $namespace);
            }
            $this->triggerEvent('phpdox.namespaces.end', $this->namespaces);
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
