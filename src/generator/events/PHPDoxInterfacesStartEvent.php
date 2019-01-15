<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class PHPDoxInterfacesStartEvent extends AbstractEvent {
    private $interfaces;

    public function __construct(InterfaceCollection $interfaces) {
        $this->interfaces = $interfaces;
    }

    public function getInterfaces() {
        return $this->interfaces;
    }

    protected function getEventName() {
        return 'phpdox.interfaces.start';
    }
}
