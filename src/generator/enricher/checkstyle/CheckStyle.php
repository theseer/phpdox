<?php

namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\fDOM\fDOMDocument;
    use TheSeer\phpDox\Generator\AbstractEvent;

    class CheckStyle implements EnricherInterface {

        private $config;
        private $findings = NULL;

        public function __construct(CheckStyleConfig $config) {
            $this->config = $config;
            $this->loadFindings($config->getLogFilePath());
        }

        public function enrich(AbstractEvent $event) {

            // TODO: Implement enrich() method.
            var_dump($event->type);
        }

        private function loadFindings($xmlFile) {
            $dom = new fDOMDocument();
            $dom->load($xmlFile);
            $this->findings = array();
            foreach($dom->query('/checkstyle/file') as $file) {
                $this->findings[$file->getAttribute('name')] = $file->query('*');
            }
        }

    }

}
