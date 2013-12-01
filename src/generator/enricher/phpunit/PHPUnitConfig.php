<?php

namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\fDOM\fDOMElement;
    use TheSeer\phpDox\GeneratorConfig;

    class PHPUnitConfig {

        /**
         * @var GeneratorConfig
         */
        private $generator;

        /**
         * @var fDOMElement
         */
        private $context;

        public function __construct(GeneratorConfig $generator, fDOMElement $ctx) {
            $this->context = $ctx;
            $this->generator = $generator;
        }

    }

}
