<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class MethodCollection extends AbstractCollection {
    public function current(): MethodObject {
        return new MethodObject($this->getCurrentNode());
    }
}
