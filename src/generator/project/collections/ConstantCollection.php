<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class ConstantCollection extends AbstractCollection {
    public function current(): ConstantObject {
        return new ConstantObject($this->getCurrentNode());
    }
}
