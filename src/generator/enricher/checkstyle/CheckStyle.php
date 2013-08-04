<?php

namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\fDOM\fDOMElement;
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

        public function enrich(AbstractEvent $event) {
            if ($event instanceof ClassStartEvent) {
                $ctx = $event->getClass();
            } elseif ($event instanceof InterfaceStartEvent) {
                $ctx = $event->getInterface();
            } else {
                /** @var TraitStartEvent $event */
                $ctx = $event->getTrait();
            }
            $fileNode = $ctx->queryOne('phpdox:file');
            if (!$fileNode) {
                return;
            }
            $file = $fileNode->getAttribute('realpath');
            if (isset($this->findings[$file])) {
                $this->processFindings($ctx, $this->findings[$file]);
            }
        }

        private function loadFindings($xmlFile) {
            $dom = new fDOMDocument();
            $dom->load($xmlFile);
            $this->findings = array();
            foreach($dom->query('/checkstyle/file') as $file) {
                $this->findings[$file->getAttribute('name')] = $file->query('*');
            }
        }

        private function processFindings(fDOMElement $ctx, \DOMNodeList $findings) {
            /** @var fDOMDocument $dom */
            $dom = $ctx->ownerDocument;

            foreach($findings as $finding) {
                /** @var fDOMElement $finding */
                $line = $finding->getAttribute('line');
                $ref = $ctx->queryOne(sprintf('//phpdox:*/*[@line = %d or (@start <= %d and @end >= %d)]', $line, $line, $line));
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
