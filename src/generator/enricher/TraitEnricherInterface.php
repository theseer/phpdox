<?php
namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\phpDox\Generator\TraitStartEvent;

    interface TraitEnricherInterface extends EnricherInterface {

        public function enrichTrait(TraitStartEvent $event);

    }

}
