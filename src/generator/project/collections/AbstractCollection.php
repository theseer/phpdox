<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

use TheSeer\fDOM\fDOMElement;

abstract class AbstractCollection implements \Iterator {
    /**
     * @var \DOMNodeList
     */
    private $nodeList;

    /**
     * @var int
     */
    private $position = 0;

    public function __construct(\DOMNodeList $nodeList) {
        $this->nodeList = $nodeList;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     */
    public function next(): void {
        $this->position++;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     *
     * @return mixed scalar on success, or null on failure
     */
    public function key() {
        return $this->position;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     *
     * @return bool The return value will be casted to boolean and then evaluated.
     *              Returns true on success or false on failure.
     */
    public function valid(): bool {
        return $this->nodeList->length > $this->position;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     */
    public function rewind(): void {
        $this->position = 0;
    }

    protected function getCurrentNode(): fDOMElement {
        return $this->nodeList->item($this->position);
    }
}
