<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class PHPDoxEndEvent extends AbstractEvent {
    private $index;

    private $tree;

    public function __construct(Index $index, SourceTree $tree) {
        $this->index = $index;
        $this->tree  = $tree;
    }

    public function getIndex() {
        return $this->index;
    }

    public function getTree() {
        return $this->tree;
    }

    protected function getEventName() {
        return 'phpdox.end';
    }
}
