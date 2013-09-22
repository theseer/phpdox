<?php
namespace TheSeer\phpDox\Generator {

    use TheSeer\fDOM\fDOMElement;

    class ConstantObject {

        /**
         * @var fDOMElement
         */
        private $node;

        public function __construct(fDOMElement $node) {
            $this->node = $node;
        }

        public function getName() {
            return $this->node->getAttribute('name');
        }

        public function getValue() {
            return $this->node->getAttribute('value');
        }

    }

}
