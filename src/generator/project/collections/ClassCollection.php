<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class ClassCollection extends AbstractCollection {
    public function current(): ClassEntry {
        return new ClassEntry($this->getCurrentNode());
    }
}
