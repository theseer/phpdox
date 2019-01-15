<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class ClassObject extends AbstractUnitObject {
    public function getMembers(): MemberCollection {
        return new MemberCollection($this->asDom()->query('phpdox:member'));
    }
}
