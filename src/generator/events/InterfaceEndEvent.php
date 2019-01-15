<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class InterfaceEndEvent extends AbstractEvent {
    private $interface;

    public function __construct(InterfaceObject $interface) {
        $this->interface = $interface;
    }

    public function getInterface() {
        return $this->interface;
    }

    protected function getEventName() {
        return 'interface.end';
    }
}
