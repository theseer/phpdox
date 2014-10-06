<?php
namespace TheSeer\phpDox\Generator {

    use TheSeer\phpDox\FileInfo;

    class TokenFileIterator implements \Iterator {

        /**
         * @var \DOMNodeList
         */
        private $nodeList;

        public function __construct(\DOMNodeList $nodeList) {
            $this->nodeList = $nodeList;
        }

        /**
         * @var int
         */
        private $pos = 0;

        /**
         * @return TokenFile
         */
        public function current() {
            $item = $this->nodeList->item($this->pos);
            $path = dirname(urldecode($item->ownerDocument->documentURI)) . '/' . $item->getAttribute('xml');
            return new TokenFile(new FileInfo($path));
        }

        public function next() {
            $this->pos++;
        }

        public function key() {
            return $this->pos;
        }

        public function valid() {
            return $this->nodeList->length > $this->pos;
        }

        public function rewind() {
            $this->pos = 0;
        }

    }

}
