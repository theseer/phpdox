<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Collector;

class InterfaceObject extends AbstractUnitObject {
    protected $rootName = 'interface';

    public function addImplementor(AbstractUnitObject $unit): void {
        if ($this->getRootNode()->queryOne(\sprintf('phpdox:implementor[@full = "%s"]', $unit->getName())) !== null) {
            return;
        }
        $implementor = $this->addToContainer('implementors', $unit->getType());
        $this->setName($unit->getName(), $implementor);
    }
}
