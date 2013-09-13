<?php
namespace TheSeer\phpDox\Generator {

    use TheSeer\fDOM\fDOMDocument;

    class SourceTree {

        private $dom;

        public function __construct(fDOMDocument $dom) {
            $this->dom = $dom;
        }

        public function asDom() {
            return $this->dom;
        }

    }

}
