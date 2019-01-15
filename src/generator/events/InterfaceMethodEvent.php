<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class InterfaceMethodEvent extends MethodEvent {
    private $interface;

    public function __construct(MethodObject $method, InterfaceObject $interface) {
        parent::__construct($method);
        $this->interface = $interface;
    }

    public function getInterface() {
        return $this->interface;
    }

    protected function getEventName() {
        return 'interface.method';
    }
}
