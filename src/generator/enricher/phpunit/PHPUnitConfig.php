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

        public function getCoveragePath() {
            $basedirDefault = dirname($this->context->ownerDocument->baseURI);
            $path = $basedirDefault . '/build/logs';
            if ($this->context->parentNode->hasAttribute('base')) {
                $path = $this->context->parentNode->getAttribute('base');
            }
            if ($path != '') { $path .= '/'; }
            $coverage = $this->context->queryOne('cfg:path');
            if ($coverage && $coverage->hasAttribute('name')) {
                 $path .= $coverage->getAttribute('name');
            } else {
              $coverage = $this->context->queryOne('cfg:coverage');
              if ($coverage && $coverage->hasAttribute('path')) {
                  $path .= $coverage->getAttribute('path');
              } else {
                $path .= 'coverage';
              }
            }
            return $path;
        }
    }

}
