<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class PHPDoxNamespacesEndEvent extends AbstractEvent {
    private $namespaces;

    public function __construct(NamespaceCollection $namespaces) {
        $this->namespaces = $namespaces;
    }

    public function getNamespaces() {
        return $this->namespaces;
    }

    protected function getEventName() {
        return 'phpdox.namespaces.end';
    }
}
