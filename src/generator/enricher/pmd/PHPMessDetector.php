<?php

namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\fDOM\fDOMElement;
    use TheSeer\phpDox\Generator\AbstractEvent;
    use TheSeer\phpDox\Generator\ClassStartEvent;
    use TheSeer\phpDox\Generator\InterfaceStartEvent;
    use TheSeer\phpDox\Generator\TraitStartEvent;

    class PHPMessDetector implements EnricherInterface {

        private $config;
        private $violations = NULL;

        public function __construct(PHPMessDetectorConfig $config) {
            $this->config = $config;
            $this->loadViolations($config->getLogFilePath());
        }

        /**
         * @return string
         */
        public function getName() {
            return 'PHPMessDetector XML';
        }

        public function enrich(AbstractEvent $event) {
            if ($event instanceof ClassStartEvent) {
                $ctx = $event->getClass();
            } elseif ($event instanceof InterfaceStartEvent) {
                $ctx = $event->getInterface();
            } else {
                /** @var TraitStartEvent $event */
                $ctx = $event->getTrait();
            }
            $file = $ctx->getSourceFile();
            if (isset($this->violations[$file])) {
                $this->processViolations($ctx->asDom(), $this->violations[$file]);
            }
        }

        private function loadViolations($xmlFile) {
            $this->violations = array();
            try {
                if (!file_exists($xmlFile)) {
                    throw new EnricherException(
                        sprintf('Logfile "%s" not found.', $xmlFile),
                        EnricherException::LoadError
                    );
                }
                $dom = new fDOMDocument();
                $dom->load($xmlFile);
                foreach($dom->query('/pmd/file') as $file) {
                    $this->violations[$file->getAttribute('name')] = $file->query('*');
                }
            } catch (fDOMException $e) {
                throw new EnricherException(
                    'Parsing pmd logfile failed: ' . $e->getMessage(),
                    EnricherException::LoadError
                );
            }

        }

        private function processViolations(fDOMDocument $dom, \DOMNodeList $violations) {
            foreach($violations as $violation) {
                /** @var fDOMElement $violation */
                $line = $violation->getAttribute('beginline');
                $ref = $dom->queryOne(sprintf('//phpdox:*/*[@line = %d or (@start <= %d and @end >= %d)]', $line, $line, $line));
                if (!$ref) {
                    // One src file may contain multiple classes/traits/interfaces, so the
                    // finding might not apply to the current object since violations are based on filenames
                    // but we have individual objects - so we just ignore the finding for this context
                    continue;
                }
                $container = $ref->queryOne('phpdox:enrichments');
                if(!$container) {
                    $container = $dom->createElementNS('http://xml.phpdox.de/src#', 'enrichments');
                    $ref->appendChild($container);
                }
                $enrichment = $container->queryOne('phpdox:enrichment[@source="pmd"]');
                if (!$enrichment) {
                    $enrichment = $dom->createElementNS('http://xml.phpdox.de/src#', 'enrichment');
                    $enrichment->setAttribute('source', 'pmd');
                    $container->appendChild($enrichment);
                }

                $enrichViolation = $dom->createElementNS('http://xml.phpdox.de/src#', 'violation');
                $enrichment->appendChild($enrichViolation);
                $enrichViolation->appendChild($dom->createTextNode($enrichment->nodeValue));
                foreach($violation->attributes as $attr) {
                    $enrichViolation->setAttributeNode($dom->importNode($attr, true));
                }

            }
         }
    }

}
