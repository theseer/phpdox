<?php
namespace TheSeer\phpDox\Generator {

    use TheSeer\fDOM\fDOMElement;
    use TheSeer\fDOM\fDOMDocument;

    abstract class AbstractEntry {

        /**
         * @var fDOMElement
         */
        private $node;

        public function __construct(fDOMElement $node) {
            $this->node = $node;
        }

        protected function getNode() {
            return $this->node;
        }

        protected function loadDocument($dir) {
            $path = $dir . '/' . $this->getNode()->getAttribute('xml');
            $classDom = new fDOMDocument();
            $classDom->load($path);
            $classDom->registerNamespace('phpdox', 'http://xml.phpdox.de/src#');
            return $classDom;
        }
    }

}
