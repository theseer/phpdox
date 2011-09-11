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

    use \TheSeer\fDOM\fDOMDocument;
    use \TheSeer\fDOM\fDOMElement;

    class EventGenerator extends AbstractGenerator {

        /**
         * Map of registered Builder
         *
         * @var array
         */
        protected $eventBuilder = array(
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

        public function registerBuilder($event, EventBuilderInterface $builder) {
            if (!array_key_exists($event, $this->eventBuilder)) {
                throw new GeneratorException("'$event' unknown", GeneratorException::UnknownEvent);
            }
            $hash = spl_object_hash($builder);
            if (isset($this->eventBuilder[$event][$hash])) {
                throw GeneratorException("Builder already registered for event '$event'", GeneratorException::AlreadyRegistered);
            }
            $this->eventBuilder[$event][] = $builder;
        }

        /**
         * Main executer of the generator
         *
         * @param Array          $builderMap
         * @param ProgressLogger $logger
         */
        public function run(Array $builderMap, ProgressLogger $logger) {
            $this->logger = $logger;

            $eventFactory = $this->factory->getInstanceFor('EventFactory');
            foreach(array_keys($this->eventBuilder) as $event) {
                $this->factory->addFactory($event, $eventFactory);
            }

            foreach($builderMap as $name => $cfg) {
                $this->logger->log("Activating builder '$name'");
                $cfg->getBuilder()->setUp($this);
            }

            $this->triggerEvent('phpdox.start', $this->namespaces, $this->classes, $this->interfaces);
            if ($this->namespaces->documentElement->hasChildNodes()) {
                $this->processWithNamespace();
            } else {
                $this->processGlobalOnly();
            }
            $this->triggerEvent('phpdox.end', $this->namespaces, $this->classes, $this->interfaces);
            $logger->completed();
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

        /**
         * Proxy function to forward instance requests from builder to factory
         *
         * @param $class
         */
        public function getInstanceFor($class) {
            return call_user_func_array(array($this->factory,'getInstanceFor'), func_get_args());
        }

        protected function processClass(fDOMElement $class) {
            $classDom = $this->loadDataFile($class->getAttribute('xml'));
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

        protected function triggerEvent($eventName) {
            $this->logger->progress('processed');
            $payload = func_get_args();
            array_shift($payload);
            $event = $this->factory->getInstanceFor($eventName, $payload);
            foreach($this->eventBuilder[$eventName] as $proc) {
                $proc->handle($event);
            }
        }

    }

}
