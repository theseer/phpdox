<?php
namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\phpDox\Generator\AbstractEvent;
    use TheSeer\phpDox\Generator\ClassStartEvent;
    use TheSeer\phpDox\Generator\InterfaceStartEvent;
    use TheSeer\phpDox\Generator\TraitStartEvent;

    class PHPUnit implements ClassEnricherInterface, InterfaceEnricherInterface, TraitEnricherInterface {

        /**
         * @var fDOMDocument
         */
        private $dom;

        private $config;

        public function __construct(PHPUnitConfig $config) {
            $this->config = $config;
            $this->loadXML($config->getLogFilePath());
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

        private function loadXML($fname) {
            try {
                if (!file_exists($fname)) {
                    throw new EnricherException(
                        sprintf('PHPUnit xml file "%s" not found.', $fname),
                        EnricherException::LoadError
                    );
                }
                $this->dom = new fDOMDocument();
                $this->dom->load($fname);
            } catch (fDOMException $e) {
                throw new EnricherException(
                    'Parsing PHPUnit xml file failed: ' . $e->getMessage(),
                    EnricherException::LoadError
                );
            }
        }

    }

}
