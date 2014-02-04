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

        public function hasNamespaces() {
            return $this->dom->queryOne('count(//phpdox:namespace)') > 0;
        }

        public function hasInterfaces() {
            return $this->dom->queryOne('count(//phpdox:interface)') > 0;
        }

        public function hasTraits() {
            return $this->dom->queryOne('count(//phpdox:trait)') > 0;
        }

        public function hasClasses() {
            return $this->dom->queryOne('count(//phpdox:class)') > 0;
        }

    }

}
