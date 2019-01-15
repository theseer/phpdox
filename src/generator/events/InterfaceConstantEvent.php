<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class InterfaceConstantEvent extends ConstantEvent {
    private $interface;

    public function __construct(ConstantObject $constant, InterfaceObject $interface) {
        parent::__construct($constant);
        $this->interface = $interface;
    }

    public function getInterface() {
        return $this->interface;
    }

    protected function getEventName() {
        return 'interface.constant';
    }
}
