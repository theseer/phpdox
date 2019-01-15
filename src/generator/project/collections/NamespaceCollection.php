<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class NamespaceCollection extends AbstractCollection {
    public function current(): NamespaceEntry {
        return new NamespaceEntry($this->getCurrentNode());
    }
}
