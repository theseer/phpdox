<?php
namespace TheSeer\phpDox\Generator {

    use TheSeer\fDOM\fDOMElement;

    class MethodObject {

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

        public function getName() {
            return $this->node->getAttribute('name');
        }

        public function isPublic() {
            return $this->node->getAttribute('visibility', 'public') == 'public';
        }

        public function isPrivate() {
            return $this->node->getAttribute('visibility', 'public') == 'private';
        }

        public function isProtected() {
            return $this->node->getAttribute('visibility', 'public') == 'protected';
        }

    }

}
