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

            $container = $ctx->queryOne('phpdox:enrichments');
            if(!$container) {
                $container = $dom->createElementPrefix('phpdox','enrichments');
                $ctx->appendChild($container);
            }
            $enrichment = $dom->createElementPrefix('phpdox', 'enrichment');
            $enrichment->setAttribute('type', 'checkstyle');
            $container->appendChild($enrichment);

            foreach($findings as $f) {
                $enrichment->appendChild(
                    $dom->importNode($f)
                );
            }
        }
    }

}
