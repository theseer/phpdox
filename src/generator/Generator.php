<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

use TheSeer\phpDox\Generator\Engine\EngineInterface;
use TheSeer\phpDox\Generator\Engine\EventHandlerRegistry;
use TheSeer\phpDox\Generator\Enricher\ClassEnricherInterface;
use TheSeer\phpDox\Generator\Enricher\EndEnricherInterface;
use TheSeer\phpDox\Generator\Enricher\EnricherInterface;
use TheSeer\phpDox\Generator\Enricher\InterfaceEnricherInterface;
use TheSeer\phpDox\Generator\Enricher\StartEnricherInterface;
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
    private $enrichers = [
        'phpdox.start'     => [],
        'class.start'      => [],
        'trait.start'      => [],
        'interface.start'  => [],
        'token.file.start' => [],
        'phpdox.end'       => []
    ];

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

    public function __construct(ProgressLogger $logger, EventHandlerRegistry $registry) {
        $this->logger          = $logger;
        $this->handlerRegistry = $registry;
    }

    /**
     * @throws
     * @throws GeneratorException
     */
    public function addEngine(EngineInterface $engine): void {
        $engine->registerEventHandlers($this->handlerRegistry);
    }

    public function addEnricher(EnricherInterface $enricher): void {
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

    public function run(Project $project): void {
        $this->xmlDir  = $project->getXmlDir();
        $this->project = $project;

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

    private function processTokenFiles(SourceTree $sourceTree): void {
        foreach ($sourceTree as $tokenFile) {
            $this->handleEvent(new TokenFileStartEvent($tokenFile));

            foreach ($tokenFile as $sourceLine) {
                $this->handleEvent(new TokenLineStartEvent($tokenFile, $sourceLine));

                foreach ($sourceLine as $token) {
                    $this->handleEvent(new TokenEvent($sourceLine, $token));
                }
                $this->handleEvent(new TokenLineEndEvent($tokenFile, $sourceLine));
            }
            $this->handleEvent(new TokenFileEndEvent($tokenFile));
        }
    }

    /**
     * @param bool $progress
     */
    private function handleEvent(AbstractEvent $event, $progress = true): void {
        $eventType = $event->getType();

        if (isset($this->enrichers[$eventType])) {
            foreach ($this->enrichers[$eventType] as $enricher) {
                switch ($eventType) {
                    case 'phpdox.start':
                        {
                            $enricher->enrichStart($event);

                            break;
                        }
                    case 'class.start':
                        {
                            $enricher->enrichClass($event);

                            break;
                        }
                    case 'interface.start':
                        {
                            $enricher->enrichInterface($event);

                            break;
                        }
                    case 'trait.start':
                        {
                            $enricher->enrichTrait($event);

                            break;
                        }
                    case 'token.file.start':
                        {
                            $enricher->enrichTokenFile($event);

                            break;
                        }
                    case 'phpdox.end':
                        {
                            $enricher->enrichEnd($event);

                            break;
                        }
                }
            }
        }

        foreach ($this->handlerRegistry->getHandlersForEvent($event->getType()) as $callback) {
            \call_user_func($callback, $event);
        }

        if ($progress) {
            $this->logger->progress('processed');
        }
    }

    private function processGlobalOnly(): void {
        $classes = $this->project->getClasses();
        $this->handleEvent(new PHPDoxClassesStartEvent($classes));

        foreach ($classes as $class) {
            $this->processClass($class);
        }
        $this->handleEvent(new PHPDoxClassesEndEvent($classes));

        $traits = $this->project->getTraits();
        $this->handleEvent(new PHPDoxTraitsStartEvent($traits));

        foreach ($traits as $trait) {
            $this->processTrait($trait);
        }
        $this->handleEvent(new PHPDoxTraitsEndEvent($traits));

        $interfaces = $this->project->getInterfaces();
        $this->handleEvent(new PHPDoxInterfacesStartEvent($interfaces));

        foreach ($interfaces as $interface) {
            $this->processInterface($interface);
        }
        $this->handleEvent(new PHPDoxInterfacesEndEvent($interfaces));
    }

    private function processWithNamespace(): void {
        $namespaces = $this->project->getNamespaces();
        $this->handleEvent(new PHPDoxNamespacesStartEvent($namespaces));

        foreach ($namespaces as $namespace) {
            $this->handleEvent(new NamespaceStartEvent($namespace));

            $classes = $namespace->getClasses();
            $this->handleEvent(new NamespaceClassesStartEvent($classes, $namespace));

            foreach ($classes as $class) {
                $this->processClass($class);
            }
            $this->handleEvent(new NamespaceClassesEndEvent($classes, $namespace));

            $traits = $namespace->getTraits();
            $this->handleEvent(new NamespaceTraitsStartEvent($traits, $namespace));

            foreach ($traits as $trait) {
                $this->processTrait($trait);
            }
            $this->handleEvent(new NamespaceTraitsEndEvent($traits, $namespace));

            $interfaces = $namespace->getInterfaces();
            $this->handleEvent(new NamespaceInterfacesStartEvent($interfaces, $namespace));

            foreach ($interfaces as $interface) {
                $this->processInterface($interface);
            }
            $this->handleEvent(new NamespaceInterfacesEndEvent($interfaces, $namespace));

            $this->handleEvent(new NamespaceEndEvent($namespace));
        }
        $this->handleEvent(new PHPDoxNamespacesEndEvent($namespaces));
    }

    private function processClass(ClassEntry $entry): void {
        $class = $entry->getClassObject($this->xmlDir);
        $this->handleEvent(new ClassStartEvent($class));

        foreach ($class->getConstants() as $constant) {
            $this->handleEvent(new ClassConstantEvent($constant, $class));
        }

        foreach ($class->getMembers() as $member) {
            $this->handleEvent(new ClassMemberEvent($member, $class));
        }

        foreach ($class->getMethods() as $method) {
            $this->handleEvent(new ClassMethodEvent($method, $class));
        }
        $this->handleEvent(new ClassEndEvent($class));
    }

    private function processTrait(TraitEntry $traitEntry): void {
        $trait = $traitEntry->getTraitObject($this->xmlDir);
        $this->handleEvent(new TraitStartEvent($trait));

        foreach ($trait->getConstants() as $constant) {
            $this->handleEvent(new TraitConstantEvent($constant, $trait));
        }

        foreach ($trait->getMembers() as $member) {
            $this->handleEvent(new TraitMemberEvent($member, $trait));
        }

        foreach ($trait->getMethods() as $method) {
            $this->handleEvent(new TraitMethodEvent($method, $trait));
        }
        $this->handleEvent(new TraitEndEvent($trait));
    }

    private function processInterface(InterfaceEntry $interfaceEntry): void {
        $interface = $interfaceEntry->getInterfaceObject($this->xmlDir);

        $this->handleEvent(new InterfaceStartEvent($interface));

        foreach ($interface->getConstants() as $constant) {
            $this->handleEvent(new InterfaceConstantEvent($constant, $interface));
        }

        foreach ($interface->getMethods() as $method) {
            $this->handleEvent(new InterfaceMethodEvent($method, $interface));
        }

        $this->handleEvent(new InterfaceEndEvent($interface));
    }
}

class GeneratorException extends \Exception {
    public const UnknownEvent = 1;

    public const AlreadyRegistered = 2;
}
