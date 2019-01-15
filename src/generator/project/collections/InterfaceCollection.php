<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class InterfaceCollection extends AbstractCollection {
    public function current(): InterfaceEntry {
        return new InterfaceEntry($this->getCurrentNode());
    }
}
