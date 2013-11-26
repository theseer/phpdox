<?php
namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\phpDox\Generator\AbstractEvent;
    use TheSeer\phpDox\Generator\ClassStartEvent;
    use TheSeer\phpDox\Generator\InterfaceStartEvent;
    use TheSeer\phpDox\Generator\TraitStartEvent;

    class PHPUnit implements ClassEnricherInterface, InterfaceEnricherInterface, TraitEnricherInterface {

        private $config;

        public function __construct(PHPUnitConfig $config) {
            $this->config = $config;
            $this->loadIndex($config->getLogFilePath());
        }

        /**
         * @return string
         */
        public function getName() {
            return 'PHPUnit Coverage XML';
        }

        public function enrichClass(ClassStartEvent $event) {
            // TODO: Implement enrichClass() method.
        }

        public function enrichInterface(InterfaceStartEvent $event) {
            // TODO: Implement enrichInterface() method.
        }

        public function enrichTrait(TraitStartEvent $event) {
            // TODO: Implement enrichTrait() method.
        }

    }

}
