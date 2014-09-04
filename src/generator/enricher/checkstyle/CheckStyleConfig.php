<?php

namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\fDOM\fDOMElement;
    use TheSeer\phpDox\GeneratorConfig;

    class CheckStyleConfig {

        /**
         * @var GeneratorConfig
         */
        private $generator;

        /**
         * @var fDOMElement
         */
        private $context;


        protected $defaultFileName = 'checkstyle.xml';

        public function __construct(GeneratorConfig $generator, fDOMElement $ctx) {
            $this->context = $ctx;
            $this->generator = $generator;
        }

        public function getLogFilePath() {
            $basedirDefault = dirname($this->context->ownerDocument->baseURI);
            $path = $basedirDefault . '/build/logs';
            if ($this->context->parentNode->hasAttribute('base')) {
                $path = $this->context->parentNode->getAttribute('base');
            }
            if ($path != '') { $path .= '/'; }
            $file = $this->context->queryOne('cfg:file');
            if ($file && $file->hasAttribute('name')) {
                $path .= $file->getAttribute('name');
            } else {
                $path .= $this->defaultFileName;
            }
            return $path;
        }
    }

}
