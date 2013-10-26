<?php
namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\phpDox\Generator\ClassStartEvent;

    interface ClassEnricherInterface extends EnricherInterface {

        public function enrichClass(ClassStartEvent $event);

    }

}
