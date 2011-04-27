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
    use \TheSeer\fXSL\fXSLTProcessor;

    class Generator {
        protected $xmlDir;
        protected $docDir;

        protected $publicOnly = false;

        protected $namespaces;
        protected $interfaces;
        protected $classes;

        protected $eventProcessors = array(
            'phpdox.before' => array(),
            'phpdox.after' => array(),

            'namespace.before' => array(),
            'namespace.classes.before' => array(),
            'namespace.classes.after' => array(),
            'namespace.interfaces.before' => array(),
            'namespace.interfaces.after' => array(),
            'namespace.after' => array(),

            'class.before' => array(),
            'class.constant' => array(),
            'class.member' => array(),
            'class.method' => array(),
            'class.after' => array(),

            'interface.before' => array(),
            'interface.constant' => array(),
            'interface.method' => array(),
            'interface.after' => array()
        );

        /**
         * Generator constructor
         *
         * @param string $xmlDir      Base path where class xml files are found
         * @param string $docDir      Base directory to store documentation files in
         * @param fDomDocument $nsDom DOM instance to register namespaces in
         * @param fDomDocument $iDom  DOM instance to register interfaces in
         * @param fDomDocument $cDom  DOM instance to register classes in
         */
        public function __construct($xmlDir, $docDir, fDOMDocument $nsDom, fDOMDocument $iDom, fDOMDocument $cDom) {
            $this->xmlDir  = $xmlDir;
            $this->docDir  = $docDir;

            $this->namespaces = $nsDom;
            $this->interfaces = $iDom;
            $this->classes    = $cDom;
        }

        public function setPublicOnly($switch) {
            $this->publicOnly = $switch;
        }

        public function registerProcessor($event, Processor $processor) {
            if (!array_key_exists($event, $this->eventProcessors)) {
                throw GeneratorException("'$event' unknown", GeneratorException::UnkownEvent);
            }
            $hash = spl_object_hash($processor);
            if (isset($this->eventProcessors[$event][$hash])) {
                throw GeneratorException("Processor already registered for event '$event'", GeneratorException::AlreadyRegistered);
            }
            $this->eventProcessors[$event][] = $processor;
        }

        /**
         * Main executer of the generator
         *
         */
        public function run() {
            $this->handleEvent('phpdox.before');

            foreach($this->namespaces->query('//phpdox:namespace') as $namespace) {
                $this->handleEvent('namespace.before', $namespace);
                $this->handleEvent('namespace.classes.before', $namespace);

                $xpath = sprintf('//phpdox:namespace[@name="%s"]/phpdox:class', $namespace->getAttribute('name'));
                foreach($this->classes->query($xpath) as $class) {
                    $this->processClass($class);
                }

                $this->handleEvent('namespace.classes.after', $namespace);
                $this->handleEvent('namespace.interfaces.before', $namespace);

                $xpath = sprintf('//phpdox:namespace[@name="%s"]/phpdox:interface', $namespace->getAttribute('name'));
                foreach($this->interfaces->query($xpath) as $interface) {
                    $this->processInterface($interface);
                }

                $this->handleEvent('namespace.interfaces.after', $namespace);
                $this->handleEvent('namespace.after', $namespace);
            }

            $this->handleEvent('phpdox.after');
        }

        protected function processClass(fDOMElement $class) {
            $classDom = new fDomDocument();
            $classDom->load($this->xmlDir . '/' . $class->getAttribute('xml'));
            $classDom->registerNamespace('phpdox', 'http://xml.phpdox.de/src#');

            foreach($classDom->query('//phpdox:class') as $classNode) {
                $this->handleEvent('class.before', $classNode);

                foreach($classNode->query('phpdox:constant') as $constant) {
                    $this->handleEvent('class.constant', $constant);
                }

                foreach($classNode->query('phpdox:member') as $member) {
                    if ($this->publicOnly && ($member->getAttribute('visibility')!='public')) {
                        continue;
                    }
                    $this->handleEvent('class.member', $member);
                }

                foreach($classNode->query('phpdox:method') as $method) {
                    if ($this->publicOnly && ($method->getAttribute('visibility')!='public')) {
                        continue;
                    }
                    $this->handleEvent('class.method', $method);
                }
                $this->handleEvent('class.after', $classNode);
            }
        }

        protected function processInterface(fDOMElement $interface) {
            $interfaceDom = new fDomDocument();
            $interfaceDom->load($this->xmlDir . '/' . $interface->getAttribute('xml'));
            $interfaceDom->registerNamespace('phpdox', 'http://xml.phpdox.de/src#');

            foreach($interfaceDom->query('//phpdox:interface') as $interfaceNode) {
                $this->handleEvent('interface.before', $interfaceNode);

                foreach($interfaceNode->query('phpdox:constant') as $constant) {
                    $this->handleEvent('interface.constant', $constant);
                }

                foreach($interfaceNode->query('phpdox:method') as $method) {
                    $this->handleEvent('interface.method', $method);
                }

                $this->handleEvent('interface.after', $interfaceNode);
            }
        }

        protected function handleEvent($event) {
            $payload = func_get_args();
            echo "$event\n";
        }

    }

    class GeneratorException extends \Exception {
        const UnknownEvent = 1;
        const AlreadyRegistered = 2;
    }

}