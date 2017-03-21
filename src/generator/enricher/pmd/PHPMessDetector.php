<?php

namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\fDOM\fDOMElement;
    use TheSeer\fDOM\fDOMException;
    use TheSeer\phpDox\FileInfo;
    use TheSeer\phpDox\Generator\AbstractUnitObject;
    use TheSeer\phpDox\Generator\ClassStartEvent;
    use TheSeer\phpDox\Generator\InterfaceStartEvent;
    use TheSeer\phpDox\Generator\TraitStartEvent;

    class PHPMessDetector extends AbstractEnricher implements ClassEnricherInterface, InterfaceEnricherInterface, TraitEnricherInterface {

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

        public function enrichClass(ClassStartEvent $event) {
            $this->enrichUnit($event->getClass());
        }

        public function enrichInterface(InterfaceStartEvent $event) {
            $this->enrichUnit($event->getInterface());
        }

        public function enrichTrait(TraitStartEvent $event) {
            $this->enrichUnit($event->getTrait());
        }

        public function enrichUnit(AbstractUnitObject $ctx) {
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
                    $fileInfo = new FileInfo($file->getAttribute('name'));
                    $this->violations[$fileInfo->getPathname()] = $file->query('*');
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

                $enrichment = $this->getEnrichtmentContainer($ref, 'pmd');
                $enrichViolation = $dom->createElementNS(self::XMLNS, 'violation');
                $enrichment->appendChild($enrichViolation);
                $enrichViolation->setAttribute('message', trim($violation->nodeValue));
                foreach($violation->attributes as $attr) {
                    $enrichViolation->setAttributeNode($dom->importNode($attr, true));
                }

            }
         }
    }

}
