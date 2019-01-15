<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator\Enricher;

use TheSeer\phpDox\Generator\PHPDoxStartEvent;

interface StartEnricherInterface extends EnricherInterface {
    public function enrichStart(PHPDoxStartEvent $event);
}
