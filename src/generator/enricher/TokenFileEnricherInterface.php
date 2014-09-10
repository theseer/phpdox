<?php
namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\phpDox\Generator\TokenFileStartEvent;

    interface TokenFileEnricherInterface extends EnricherInterface {

        public function enrichTokenFile(TokenFileStartEvent $event);

    }

}
