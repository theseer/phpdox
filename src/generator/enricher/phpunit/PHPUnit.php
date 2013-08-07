<?php
namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\phpDox\Generator\AbstractEvent;

    class PHPUnit implements EnricherInterface {

        /**
         * @return string
         */
        public function getName() {
            return 'PHPUnit Coverage XML';
        }

        public function enrich(AbstractEvent $event) {
            // TODO: Implement enrich() method.
        }

    }

}
