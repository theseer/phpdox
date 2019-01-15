<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator\Enricher;

use TheSeer\phpDox\Generator\InterfaceStartEvent;

interface InterfaceEnricherInterface extends EnricherInterface {
    public function enrichInterface(InterfaceStartEvent $event);
}
