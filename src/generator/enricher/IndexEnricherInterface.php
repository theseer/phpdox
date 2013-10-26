<?php
namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\phpDox\Generator\PHPDoxStartEvent;

    interface IndexEnricherInterface extends EnricherInterface {

        public function enrichIndex(PHPDoxStartEvent $event);

    }

}
