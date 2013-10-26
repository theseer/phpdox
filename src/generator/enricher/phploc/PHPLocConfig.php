<?php

namespace TheSeer\phpDox\Generator\Enricher {

    use TheSeer\fDOM\fDOMElement;
    use TheSeer\phpDox\GeneratorConfig;

    class PHPLocConfig {

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

        public function getLogFilePath() {
            $path = '';
            if ($this->context->parentNode->hasAttribute('base')) {
                $basedirDefault = dirname($this->context->ownerDocument->baseURI);
                $path = $this->context->parentNode->getAttribute('base', $basedirDefault . '/build/logs');
            }
            if ($path != '') { $path .= '/'; }
            $file = $this->context->queryOne('cfg:file');
            if ($file && $file->hasAttribute('name')) {
                $path .= $file->getAttribute('name');
            } else {
                $path .= 'phploc.xml';
            }
            return $path;
        }

    }

}
