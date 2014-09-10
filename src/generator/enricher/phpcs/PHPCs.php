<?php

namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\fDOM\fDOMElement;
    use TheSeer\fDOM\fDOMException;
    use TheSeer\phpDox\Generator\AbstractUnitObject;
    use TheSeer\phpDox\Generator\ClassStartEvent;
    use TheSeer\phpDox\Generator\InterfaceStartEvent;
    use TheSeer\phpDox\Generator\TraitStartEvent;

    class PHPCs extends CheckStyle {

        private $config;
        private $findings = NULL;
        const XMLNS = 'http://xml.phpdox.net/src';

        public function __construct(PHPCsConfig $config) {
            $this->config = $config;
            $this->loadFindings($config->getLogFilePath());
        }

        /**
         * @return string
         */
        public function getName() {
            return 'PHPCS XML';
        }

        private function loadFindings($xmlFile) {
            $this->findings = array();
            try {
                if (!file_exists($xmlFile)) {
                    throw new EnricherException(
                        sprintf('Logfile "%s" not found.', $xmlFile),
                        EnricherException::LoadError
                    );
                }
                $dom = new fDOMDocument();
                $dom->load($xmlFile);
                foreach($dom->query('/phpcs/file') as $file) {
                    $this->findings[$file->getAttribute('name')] = $file->query('*');
                }
            } catch (fDOMException $e) {
                throw new EnricherException(
                    'Parsing phpcs logfile failed: ' . $e->getMessage(),
                    EnricherException::LoadError
                );
            }
        }

        protected function processFinding(fDOMDocument $dom, $ref, \DOMElement $finding) {
            $enrichment = $this->getEnrichtmentContainer($ref, 'checkstyle');
            $enrichFinding = $dom->createElementNS(self::XMLNS, $finding->tagName);
            $enrichment->appendChild($enrichFinding);
            foreach($finding->attributes as $attr) {
                if ($attr->localName == 'severity') {
                    continue;
                }
                $enrichFinding->setAttributeNode($dom->importNode($attr, true));
            }
            $enrichFinding->setAttribute('message', $finding->nodeValue);
        }
    }

}
