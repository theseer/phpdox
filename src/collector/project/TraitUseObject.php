<?php
namespace TheSeer\phpDox\Collector {

    use TheSeer\fDOM\fDOMElement;

    /**
     *
     */
    class TraitUseObject {

        const XMLNS = 'http://xml.phpdox.net/src';

        /**
         * @var fDOMElement
         *
         */
        private $ctx;

        /**
         * @param fDOMElement $ctx
         */
        public function __construct(fDOMElement $ctx) {
            $this->ctx = $ctx;
        }

        public function export() {
            return $this->ctx;
        }

        /**
         * @param string $name
         */
        public function setName($name) {
            $this->ctx->setAttribute('name', $name);
        }

        public function getName() {
            return $this->ctx->getAttribute('name');
        }
        /**
         * @param int $startLine
         */
        public function setStartLine($startLine) {
            $this->ctx->setAttribute('start', $startLine);
        }

        /**
         * @param int $endLine
         */
        public function setEndLine($endLine) {
            $this->ctx->setAttribute('end', $endLine);
        }


        public function addAlias($originalName, $newName, $newModifier = NULL) {
            $alias = $this->ctx->appendElementNS(self::XMLNS, 'alias');
            $alias->setAttribute('method', $originalName);
            $alias->setAttribute('as', $newName);
            if ($newModifier !== NULL) {
                $alias->setAttribute('modifier', $newModifier);
            }
        }

        public function addExclude($methodName) {
            $exclude = $this->ctx->appendElementNS(self::XMLNS, 'exclude');
            $exclude->setAttribute('method', $methodName);
        }
    }

}
