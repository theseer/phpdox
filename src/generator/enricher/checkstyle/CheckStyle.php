<?php

namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\fDOM\fDOMElement;
    use TheSeer\fDOM\fDOMException;
    use TheSeer\phpDox\Generator\AbstractEvent;
    use TheSeer\phpDox\Generator\ClassStartEvent;
    use TheSeer\phpDox\Generator\InterfaceStartEvent;
    use TheSeer\phpDox\Generator\TraitStartEvent;

    class CheckStyle implements EnricherInterface {

        private $config;
        private $findings = NULL;

        public function __construct(CheckStyleConfig $config) {
            $this->config = $config;
            $this->loadFindings($config->getLogFilePath());
        }

        /**
         * @return string
         */
        public function getName() {
            return 'CheckStyle XML';
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
            if ($ctx instanceof fDOMElement) {
                debug_print_backtrace();
                die();
            }
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
                foreach($dom->query('/checkstyle/file') as $file) {
                    $this->findings[$file->getAttribute('name')] = $file->query('*');
                }
            } catch (fDOMException $e) {
                throw new EnricherException(
                    'Parsing checkstyle logfile failed: ' . $e->getMessage(),
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
                $container = $ref->queryOne('phpdox:enrichments');
                if(!$container) {
                    $container = $dom->createElementNS('http://xml.phpdox.de/src#', 'enrichments');
                    $ref->appendChild($container);
                }
                $enrichment = $container->queryOne('phpdox:enrichment[@source="checkstyle"]');
                if (!$enrichment) {
                    $enrichment = $dom->createElementNS('http://xml.phpdox.de/src#', 'enrichment');
                    $enrichment->setAttribute('source', 'checkstyle');
                    $container->appendChild($enrichment);
                }

                $enrichFinding = $dom->createElementNS('http://xml.phpdox.de/src#', $finding->localName);
                $enrichment->appendChild($enrichFinding);
                foreach($finding->attributes as $attr) {
                    $enrichFinding->setAttributeNode($dom->importNode($attr, true));
                }
            }
        }
    }

}
