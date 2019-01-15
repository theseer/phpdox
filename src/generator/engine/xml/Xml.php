<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator\Engine;

use TheSeer\phpDox\BuildConfig;
use TheSeer\phpDox\Generator\AbstractEvent;
use TheSeer\phpDox\Generator\ClassStartEvent;
use TheSeer\phpDox\Generator\InterfaceStartEvent;
use TheSeer\phpDox\Generator\PHPDoxEndEvent;
use TheSeer\phpDox\Generator\TokenFileStartEvent;
use TheSeer\phpDox\Generator\TraitStartEvent;

class Xml extends AbstractEngine {
    private $outputDir;

    public function __construct(BuildConfig $config) {
        $this->outputDir = $config->getOutputDirectory();
    }

    public function registerEventHandlers(EventHandlerRegistry $registry): void {
        $registry->addHandler('phpdox.end', $this, 'handleIndex');
        $registry->addHandler('class.start', $this, 'handle');
        $registry->addHandler('trait.start', $this, 'handle');
        $registry->addHandler('interface.start', $this, 'handle');
        $registry->addHandler('token.file.start', $this, 'handleToken');
    }

    public function handle(AbstractEvent $event): void {
        if ($event instanceof ClassStartEvent) {
            $ctx  = $event->getClass();
            $path = 'classes';
        } else {
            if ($event instanceof TraitStartEvent) {
                $ctx  = $event->getTrait();
                $path = 'traits';
            } else {
                if ($event instanceof InterfaceStartEvent) {
                    $ctx  = $event->getInterface();
                    $path = 'interfaces';
                } else {
                    throw new EngineException(
                        'Unexpected Event of type ' . \get_class($event),
                        XMLEngineException::UnexpectedType
                    );
                }
            }
        }
        $dom = $ctx->asDom();
        $this->saveDomDocument(
            $dom,
            $this->outputDir . '/' . $path . '/' . \str_replace('\\', '_', $dom->documentElement->getAttribute('full')) . '.xml'
        );
    }

    public function handleIndex(PHPDoxEndEvent $event): void {
        $dom = $event->getIndex()->asDom();
        $this->saveDomDocument($dom, $this->outputDir . '/index.xml');
    }

    public function handleToken(TokenFileStartEvent $event): void {
        $dom = $event->getTokenFile()->asDom();
        $this->saveDomDocument(
            $dom,
            $this->outputDir . '/tokens/' . $dom->queryOne('//phpdox:file')->getAttribute('relative') . '.xml'
        );
    }
}

class XMLEngineException extends EngineException {
    public const UnexpectedType = 2;
}
