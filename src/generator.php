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
    use \TheSeer\fXSL\fXSLCallback;

    class Generator {
        protected $xmlDir;
        protected $docDir;

        protected $publicOnly = false;

        protected $logger;

        protected $namespaces;
        protected $packages;
        protected $interfaces;
        protected $classes;

        /**
         * Map of registered handler
         *
         * @var array
         */
        protected $eventHandler = array(
            'phpdox.start' => array(),
            'phpdox.end' => array(),

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

        /**
         * Generator constructor
         *
         * @param string       $xmlDir Base path where class xml files are found
         * @param string       $tplDir Base path for templates
         * @param string       $docDir Base directory to store documentation files in
         * @param fDomDocument $nsDom  DOM instance of namespaces.xml
         * @param fDomDocument $iDom   DOM instance of interfaces.xml
         * @param fDomDocument $cDom   DOM instance of classes.xml
         */
        public function __construct($xmlDir, $tplDir, $docDir, fDOMDocument $nsDom, fDOMDocument $pDom, fDOMDocument $iDom, fDOMDocument $cDom) {
            $this->xmlDir = $xmlDir;
            $this->docDir = $docDir;
            $this->tplDir = $tplDir;

            $this->namespaces = $nsDom;
            $this->packages   = $pDom;
            $this->interfaces = $iDom;
            $this->classes    = $cDom;
        }

        public function setPublicOnly($switch) {
            $this->publicOnly = $switch;
        }

        public function registerHandler($event, EventHandler $handler) {
            if (!array_key_exists($event, $this->eventHandler)) {
                throw new GeneratorException("'$event' unknown", GeneratorException::UnknownEvent);
            }
            $hash = spl_object_hash($handler);
            if (isset($this->eventHandler[$event][$hash])) {
                throw GeneratorException("Handler already registered for event '$event'", GeneratorException::AlreadyRegistered);
            }
            $this->eventHandler[$event][] = $handler;
        }

        /**
         * Main executer of the generator
         *
         * @param ProgressLogger $logger
         */
        public function run(ProgressLogger $logger) {
            $this->logger = $logger;
            $this->triggerEvent('phpdox.start');
            if ($this->namespaces->documentElement->hasChildNodes()) {
                $this->processWithNamespace();
            } else {
                $this->processGlobalOnly();
            }
            $this->triggerEvent('phpdox.end');
            $logger->completed();
        }

        protected function processGlobalOnly() {
            $this->triggerEvent('phpdox.classes.start');
            foreach($this->classes->query('//phpdox:class') as $class) {
                $this->processClass($class);
            }
            $this->triggerEvent('phpdox.classes.end');
            $this->triggerEvent('phpdox.interfaces.start');
            foreach($this->interfaces->query('//phpdox:interface') as $interface) {
                $this->processInterface($interface);
            }
            $this->triggerEvent('phpdox.interfaces.end');
        }

        protected function processWithNamespace() {
            foreach($this->namespaces->query('//phpdox:namespace') as $namespace) {
                $this->triggerEvent('namespace.start', $namespace);
                $this->triggerEvent('namespace.classes.start', $namespace);

                $xpath = sprintf('//phpdox:namespace[@name="%s"]/phpdox:class', $namespace->getAttribute('name'));
                foreach($this->classes->query($xpath) as $class) {
                    $this->processClass($class);
                }

                $this->triggerEvent('namespace.classes.end', $namespace);
                $this->triggerEvent('namespace.interfaces.start', $namespace);

                $xpath = sprintf('//phpdox:namespace[@name="%s"]/phpdox:interface', $namespace->getAttribute('name'));
                foreach($this->interfaces->query($xpath) as $interface) {
                    $this->processInterface($interface);
                }

                $this->triggerEvent('namespace.interfaces.end', $namespace);
                $this->triggerEvent('namespace.end', $namespace);
            }
        }

        public function getXSLTProcessor($filename) {
            $tpl = new fDomDocument();
            $tpl->load($this->tplDir . '/' . $filename);
            return new fXSLTProcessor($tpl);
        }

        public function saveDomDocument($dom, $filename) {
            $filename = $this->docDir . '/' . $filename;
            $path = dirname($filename);
            clearstatcache();
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            return $dom->save($filename);
        }


        protected function processClass(fDOMElement $class) {
            $classDom = new fDomDocument();
            $classDom->load($this->xmlDir . '/' . $class->getAttribute('xml'));
            $classDom->registerNamespace('phpdox', 'http://xml.phpdox.de/src#');

            foreach($classDom->query('//phpdox:class') as $classNode) {
                $this->triggerEvent('class.start', $classNode);

                foreach($classNode->query('phpdox:constant') as $constant) {
                    $this->triggerEvent('class.constant', $constant);
                }

                foreach($classNode->query('phpdox:member') as $member) {
                    if ($this->publicOnly && ($member->getAttribute('visibility')!='public')) {
                        continue;
                    }
                    $this->triggerEvent('class.member', $member);
                }

                foreach($classNode->query('phpdox:method') as $method) {
                    if ($this->publicOnly && ($method->getAttribute('visibility')!='public')) {
                        continue;
                    }
                    $this->triggerEvent('class.method', $method);
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
                    $this->triggerEvent('interface.constant', $constant);
                }

                foreach($interfaceNode->query('phpdox:method') as $method) {
                    $this->triggerEvent('interface.method', $method);
                }

                $this->triggerEvent('interface.end', $interfaceNode);
            }
        }

        protected function triggerEvent($event) {
            $this->logger->progress('processed');
            $payload = func_get_args();
            foreach($this->eventHandler[$event] as $proc) {
                call_user_func_array(array($proc, 'handle'), $payload);
            }
        }

    }

    class GeneratorException extends \Exception {
        const UnknownEvent = 1;
        const AlreadyRegistered = 2;
    }

}