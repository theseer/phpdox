<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

use TheSeer\phpDox\FileInfo;

class TokenFileIterator implements \Iterator {
    /**
     * @var \DOMNodeList
     */
    private $nodeList;

    /**
     * @var int
     */
    private $pos = 0;

    public function __construct(\DOMNodeList $nodeList) {
        $this->nodeList = $nodeList;
    }

    public function current(): TokenFile {
        $item = $this->nodeList->item($this->pos);
        $path = \dirname(\str_replace('file:/', '', \urldecode($item->ownerDocument->documentURI))) . '/' . $item->getAttribute('xml');

        return new TokenFile(new FileInfo($path));
    }

    public function next(): void {
        $this->pos++;
    }

    public function key() {
        return $this->pos;
    }

    public function valid() {
        return $this->nodeList->length > $this->pos;
    }

    public function rewind(): void {
        $this->pos = 0;
    }
}
