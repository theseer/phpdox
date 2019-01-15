<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator\Enricher;

use TheSeer\phpDox\Generator\PHPDoxEndEvent;

interface EndEnricherInterface extends EnricherInterface {
    public function enrichEnd(PHPDoxEndEvent $event);
}
