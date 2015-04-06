<?php
namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\fDOM\fDOMElement;
    use TheSeer\phpDox\EnrichConfig;
    use TheSeer\phpDox\Generator\ClassStartEvent;
    use TheSeer\phpDox\Generator\InterfaceStartEvent;
    use TheSeer\phpDox\Generator\PHPDoxStartEvent;
    use TheSeer\phpDox\Generator\TokenFileStartEvent;
    use TheSeer\phpDox\Generator\TraitStartEvent;
    use TheSeer\phpDox\Version;

    class Build extends AbstractEnricher implements StartEnricherInterface,
        ClassEnricherInterface, TraitEnricherInterface, InterfaceEnricherInterface, TokenFileEnricherInterface {

        /**
         * @var array
         */
        private $enrichers;

        /**
         * @var fDOMElement
         */
        private $buildInfo;

        /**
         * @var Version
         */
        private $version;

        public function __construct(EnrichConfig $config) {
            $this->enrichers = array_keys($config->getGeneratorConfig()->getActiveEnrichSources());
            $this->version = $config->getVersion();
        }

        /**
         * @return string
         */
        public function getName() {
            return 'Build Information';
        }

        public function enrichStart(PHPDoxStartEvent $event) {
            $this->genericProcess($event->getIndex()->asDom());
        }

        public function enrichClass(ClassStartEvent $event) {
            $this->genericProcess($event->getClass()->asDom());
        }

        public function enrichInterface(InterfaceStartEvent $event) {
            $this->genericProcess($event->getInterface()->asDom());
        }

        public function enrichTrait(TraitStartEvent $event) {
            $this->genericProcess($event->getTrait()->asDom());
        }

        public function enrichTokenFile(TokenFileStartEvent $event) {
            $this->genericProcess($event->getTokenFile()->asDom());
        }

        private function genericProcess(fDOMDocument $dom) {
            $enrichment = $this->getEnrichtmentContainer($dom->documentElement, 'build');
            $enrichment->appendChild(
                $dom->importNode($this->getGeneralBuildInfo(), true)
            );
        }

        private function getGeneralBuildInfo() {
            if ($this->buildInfo != NULL) {
                return $this->buildInfo;
            }

            $dom = new fDOMDocument();
            $this->buildInfo = $dom->createDocumentFragment();

            $dateNode = $dom->createElementNS(self::XMLNS, 'date');
            $this->buildInfo->appendChild($dateNode);

            $date = new \DateTime('now');
            $dateNode->setAttribute('unix', $date->getTimestamp());
            $dateNode->setAttribute('date', $date->format('d-m-Y'));
            $dateNode->setAttribute('time', $date->format('H:i:s'));
            $dateNode->setAttribute('iso', $date->format('c'));
            $dateNode->setAttribute('rfc', $date->format('r'));

            $phpdoxNode = $dom->createElementNS(self::XMLNS, 'phpdox');
            $this->buildInfo->appendChild($phpdoxNode);

            $phpdoxNode->setAttribute('version', $this->version->getVersion());
            $phpdoxNode->setAttribute('info', $this->version->getInfoString());
            $phpdoxNode->setAttribute('generated', $this->version->getGeneratedByString());
            $phpdoxNode->setAttribute('phar', defined('PHPDOX_PHAR') ? 'yes' : 'no');

            foreach($this->enrichers as $enricher) {
                $enricherNode = $phpdoxNode->appendElementNS(self::XMLNS, 'enricher');
                $enricherNode->setAttribute('type', $enricher);
            }

            $phpNode = $dom->createElementNS(self::XMLNS, 'php');
            $this->buildInfo->appendChild($phpNode);

            $phpNode->setAttribute('version', PHP_VERSION);
            $phpNode->setAttribute('os', PHP_OS);

            foreach(get_loaded_extensions(true) as $extension) {
                $extNode = $dom->createElementNS(self::XMLNS, 'zendextension');
                $extNode->setAttribute('name', $extension);
                $phpNode->appendChild($extNode);
            }
            foreach(get_loaded_extensions(false) as $extension) {
                $extNode = $dom->createElementNS(self::XMLNS, 'extension');
                $extNode->setAttribute('name', $extension);
                $phpNode->appendChild($extNode);
            }

            return $this->buildInfo;
        }

    }

}
