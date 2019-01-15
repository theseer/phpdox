<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class MemberCollection extends AbstractCollection {
    public function current(): MemberObject {
        return new MemberObject($this->getCurrentNode());
    }
}
