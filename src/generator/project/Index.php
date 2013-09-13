<?php
namespace TheSeer\phpDox\Generator {

    use TheSeer\fDOM\fDOMDocument;

    class Index {

        private $dom;

        public function __construct(fDOMDocument $dom) {
            $this->dom = $dom;
        }

        public function asDom() {
            return $this->dom;
        }

    }

}
