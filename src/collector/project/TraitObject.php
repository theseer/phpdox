<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector;

class TraitObject extends AbstractUnitObject {
    protected $rootName = 'trait';

    public function addUser(AbstractUnitObject $unit): void {
        if ($this->getRootNode()->queryOne(\sprintf('phpdox:users/phpdox:%s[@full = "%s"]', $unit->getType(), $unit->getName())) !== null) {
            return;
        }
        $user = $this->addToContainer('users', $unit->getType());
        $this->setName($unit->getName(), $user);
    }
}
