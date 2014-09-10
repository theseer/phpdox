<?php
namespace TheSeer\phpDox\Generator {

    use TheSeer\fDOM\fDOMDocument;
    use Traversable;

    class SourceTree implements \IteratorAggregate {

        private $dom;

        public function __construct(fDOMDocument $dom) {
            $this->dom = $dom;
            $this->dom->registerNamespace('phpdox', 'http://xml.phpdox.net/src');
        }

        /**
         * @return TokenFileIterator
         */
        public function getIterator() {
            return new TokenFileIterator($this->dom->query('//phpdox:file'));
        }

        public function asDom() {
            return $this->dom;
        }

    }

}
