<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class NamespaceInterfacesEndEvent extends AbstractEvent {
    private $interfaces;

    private $namespace;

    public function __construct(InterfaceCollection $interfaces, $namespace) {
        $this->interfaces = $interfaces;
        $this->namespace  = $namespace;
    }

    public function getInterfaces() {
        return $this->interfaces;
    }

    public function getNamespace() {
        return $this->namespace;
    }

    protected function getEventName() {
        return 'namespace.interfaces.end';
    }
}
