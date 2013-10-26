<?php
namespace TheSeer\phpDox\Generator {

    class InlineCommentObject {

        /**
         * @var fDOMElement
         */
        private $node;

        public function __construct(fDOMElement $node) {
            $this->node = $node;
        }

        public function asDom() {
            return $this->node;
        }

    }

}
