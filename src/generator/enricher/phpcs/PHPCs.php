<?php

namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\fDOM\fDOMElement;
    use TheSeer\fDOM\fDOMException;
    use TheSeer\phpDox\Generator\AbstractUnitObject;
    use TheSeer\phpDox\Generator\ClassStartEvent;
    use TheSeer\phpDox\Generator\InterfaceStartEvent;
    use TheSeer\phpDox\Generator\TraitStartEvent;

    class PHPCs extends AbstractEnricher implements ClassEnricherInterface, TraitEnricherInterface, InterfaceEnricherInterface {

        private $config;
        private $findings = NULL;
        const XMLNS = 'http://xml.phpdox.net/src#';

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

        public function enrichClass(ClassStartEvent $event) {
            $this->enrichUnit($event->getClass());
        }

        public function enrichInterface(InterfaceStartEvent $event) {
            $this->enrichUnit($event->getInterface());
        }

        public function enrichTrait(TraitStartEvent $event) {
            $this->enrichUnit($event->getTrait());
        }

        private function enrichUnit(AbstractUnitObject $ctx) {
            $file = $ctx->getSourceFile();
            if (isset($this->findings[$file])) {
                $this->processFindings($ctx->asDom(), $this->findings[$file]);
            }
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

        private function processFindings(fDOMDocument $dom, \DOMNodeList $findings) {

            foreach($findings as $finding) {
                /** @var fDOMElement $finding */
                $line = $finding->getAttribute('line');
                $ref = $dom->queryOne(sprintf('//phpdox:*/*[@line = %d or (@start <= %d and @end >= %d)]', $line, $line, $line));
                if (!$ref) {
                    // One src file may contain multiple classes/traits/interfaces, so the
                    // finding might not apply to the current object since findings are based on filenames
                    // but we have individual objects - so we just ignore the finding for this context
                    continue;
                }

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

}
