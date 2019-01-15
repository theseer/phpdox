<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class TraitCollection extends AbstractCollection {
    public function current(): TraitEntry {
        return new TraitEntry($this->getCurrentNode());
    }
}
