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
            $parts = explode('\\', $name);
            $local = array_pop($parts);
            $namespace = join('\\', $parts);
            $this->ctx->setAttribute('full', $name);
            $this->ctx->setAttribute('namespace', $namespace);
            $this->ctx->setAttribute('name', $local);
        }

        public function getName() {
            return $this->ctx->getAttribute('full');
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

        public function isExcluded($methodName) {
            return $this->ctx->query(
                sprintf('phpdox:exclude[@method = "%s"]', $methodName)
            )->length > 0;
        }

        public function isAliased($methodName) {
            return $this->ctx->query(
                sprintf('phpdox:alias[@method = "%s"]', $methodName)
            )->length > 0;
        }

        public function getAliasedName($methodName) {
            return $this->getAliasNode($methodName)->getAttribute('as');
        }

        public function hasAliasedModifier($methodName) {
            return $this->getAliasNode($methodName)->hasAttribute('modifier');
        }

        public function getAliasedModifier($methodName) {
            return $this->getAliasNode($methodName)->getAttribute('modifier');
        }

        /**
         * @param $methodName
         *
         * @return mixed
         * @throws TraitUseException
         */
        private function getAliasNode($methodName) {
            $node = $this->ctx->queryOne(
                sprintf('phpdox:alias[@method = "%s"]', $methodName)
            );
            if (!$node) {
                throw new TraitUseException(
                    sprintf("Method %s not aliased", $methodName),
                    TraitUseException::NotAliased
                );
            }
            return $node;
        }

    }

}
