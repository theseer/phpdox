<?php

namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\fDOM\fDOMElement;

    class CheckStyleConfig {

        private $context;

        public function __construct(fDOMElement $ctx) {
            $this->context = $ctx;
        }

        public function getLogFilePath() {
            return 'build/logs/checkstyle.xml';
        }
    }

}
