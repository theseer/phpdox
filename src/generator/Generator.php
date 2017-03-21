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
namespace TheSeer\phpDox\Generator {

    use TheSeer\phpDox\Generator\Engine\EngineInterface;
    use TheSeer\phpDox\Generator\Engine\EventHandlerRegistry;
    use TheSeer\phpDox\Generator\Enricher\ClassEnricherInterface;
    use TheSeer\phpDox\Generator\Enricher\EndEnricherInterface;
    use TheSeer\phpDox\Generator\Enricher\EnricherInterface;
    use TheSeer\phpDox\Generator\Enricher\StartEnricherInterface;
    use TheSeer\phpDox\Generator\Enricher\InterfaceEnricherInterface;
    use TheSeer\phpDox\Generator\Enricher\TokenFileEnricherInterface;
    use TheSeer\phpDox\Generator\Enricher\TraitEnricherInterface;
    use TheSeer\phpDox\ProgressLogger;

    class Generator {

        /**
         * @var ProgressLogger
         */
        private $logger;

        /**
         * @var array
         */
        private $enrichers = array(
            'phpdox.start'       => array(),
            'class.start'        => array(),
            'trait.start'        => array(),
            'interface.start'    => array(),
            'token.file.start'   => array(),
            'phpdox.end'         => array()
        );

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
         * @var EventHandlerRegistry
         */
        private $handlerRegistry;

        /**
         * @param ProgressLogger       $logger
         * @param EventHandlerRegistry $registry
         */
        public function __construct(ProgressLogger $logger, EventHandlerRegistry $registry) {
            $this->logger = $logger;
            $this->handlerRegistry = $registry;
        }

        /**
         * @param EngineInterface $engine
         *
         * @throws
         * @throws GeneratorException
         */
        public function addEngine(EngineInterface $engine) {
            $engine->registerEventHandlers($this->handlerRegistry);
        }

        public function addEnricher(EnricherInterface $enricher) {
            if ($enricher instanceof StartEnricherInterface) {
                $this->enrichers['phpdox.start'][] = $enricher;
            }
            if ($enricher instanceof ClassEnricherInterface) {
                $this->enrichers['class.start'][] = $enricher;
            }
            if ($enricher instanceof InterfaceEnricherInterface) {
                $this->enrichers['interface.start'][] = $enricher;
            }
            if ($enricher instanceof TraitEnricherInterface) {
                $this->enrichers['trait.start'][] = $enricher;
            }
            if ($enricher instanceof TokenFileEnricherInterface) {
                $this->enrichers['token.file.start'][] = $enricher;
            }
            if ($enricher instanceof EndEnricherInterface) {
                $this->enrichers['phpdox.end'][] = $enricher;
            }
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
            $this->processTokenFiles($project->getSourceTree());
            $this->handleEvent(new PHPDoxEndEvent($project->getIndex(), $project->getSourceTree()));
            $this->logger->completed();

        }

        private function processTokenFiles(SourceTree $sourceTree) {
            foreach($sourceTree as $tokenFile) {
                $this->handleEvent(new TokenFileStartEvent($tokenFile));
                foreach($tokenFile as $sourceLine) {
                    $this->handleEvent(new TokenLineStartEvent($tokenFile, $sourceLine));
                    foreach($sourceLine as $token) {
                        $this->handleEvent(new TokenEvent($sourceLine, $token));
                    }
                    $this->handleEvent(new TokenLineEndEvent($tokenFile, $sourceLine));
                }
                $this->handleEvent(new TokenFileEndEvent($tokenFile));
            }
        }

        /**
         * @param AbstractEvent $event
         * @param bool          $progress
         */
        private function handleEvent(AbstractEvent $event, $progress = TRUE) {
            $eventType = $event->getType();
            if (isset($this->enrichers[$eventType])) {
                foreach($this->enrichers[$eventType] as $enricher) {
                    switch($eventType) {
                        case 'phpdox.start': {
                            $enricher->enrichStart($event);
                            break;
                        }
                        case 'class.start': {
                            $enricher->enrichClass($event);
                            break;
                        }
                        case 'interface.start': {
                            $enricher->enrichInterface($event);
                            break;
                        }
                        case 'trait.start': {
                            $enricher->enrichTrait($event);
                            break;
                        }
                        case 'token.file.start': {
                            $enricher->enrichTokenFile($event);
                            break;
                        }
                        case 'phpdox.end': {
                            $enricher->enrichEnd($event);
                            break;
                        }
                    }
                }
            }
            foreach($this->handlerRegistry->getHandlersForEvent($event->getType()) as $callback) {
                call_user_func($callback, $event);
            }
            if ($progress) {
                $this->logger->progress('processed');
            }
    }

        /**
         *
         */
        private function processGlobalOnly() {
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
        private function processWithNamespace() {
            $namespaces = $this->project->getNamespaces();
            $this->handleEvent(new PHPDoxNamespacesStartEvent($namespaces));

            foreach($namespaces as $namespace) {
                $this->handleEvent(new NamespaceStartEvent($namespace));

                $classes = $namespace->getClasses();
                $this->handleEvent(new NamespaceClassesStartEvent($classes, $namespace));
                foreach($classes as $class) {
                    $this->processClass($class);
                }
                $this->handleEvent(new NamespaceClassesEndEvent($classes, $namespace));

                $traits = $namespace->getTraits();
                $this->handleEvent(new NamespaceTraitsStartEvent($traits, $namespace));
                foreach($traits as $trait) {
                    $this->processTrait($trait);
                }
                $this->handleEvent(new NamespaceTraitsEndEvent($traits, $namespace));

                $interfaces = $namespace->getInterfaces();
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
         * @param $class ClassEntry
         */
        private function processClass(ClassEntry $entry) {
            $class = $entry->getClassObject($this->xmlDir);
            $this->handleEvent(new ClassStartEvent($class));

            foreach($class->getConstants() as $constant) {
                $this->handleEvent(new ClassConstantEvent($constant, $class));
            }

            foreach($class->getMembers() as $member) {
                if ($this->publicOnly && !$member->isPublic()) {
                    continue;
                }
                $this->handleEvent(new ClassMemberEvent($member, $class));
            }

            foreach($class->getMethods() as $method) {
                if ($this->publicOnly && !$method->isPublic()) {
                    continue;
                }
                $this->handleEvent(new ClassMethodEvent($method, $class));
            }
            $this->handleEvent(new ClassEndEvent($class));

        }

        /**
         * @param TraitEntry $traitEntry
         */
        private function processTrait(TraitEntry $traitEntry) {
            $trait = $traitEntry->getTraitObject($this->xmlDir);

            $this->handleEvent(new TraitStartEvent($trait));

            foreach($trait->getConstants() as $constant) {
                $this->handleEvent(new TraitConstantEvent($constant, $trait));
            }

            foreach($trait->getMembers() as $member) {
                if ($this->publicOnly && !$member->isPublic()) {
                    continue;
                }
                $this->handleEvent(new TraitMemberEvent($member, $trait));
            }

            foreach($trait->getMethods() as $method) {
                if ($this->publicOnly && !$method->isPublic()) {
                    continue;
                }
                $this->handleEvent(new TraitMethodEvent($method, $trait));
            }
            $this->handleEvent(new TraitEndEvent($trait));
        }

        /**
         * @param InterfaceEntry $interface
         */
        private function processInterface(InterfaceEntry $interfaceEntry) {
            $interface = $interfaceEntry->getInterfaceObject($this->xmlDir);

            $this->handleEvent(new InterfaceStartEvent($interface));

            foreach($interface->getConstants() as $constant) {
                $this->handleEvent(new InterfaceConstantEvent($constant, $interface));
            }

            foreach($interface->getMethods() as $method) {
                $this->handleEvent(new InterfaceMethodEvent($method, $interface));
            }

            $this->handleEvent(new InterfaceEndEvent($interface));
        }

    }

    class GeneratorException extends \Exception {
        const UnknownEvent = 1;
        const AlreadyRegistered = 2;
    }

}
