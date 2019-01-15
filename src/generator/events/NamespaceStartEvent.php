<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class NamespaceStartEvent extends AbstractEvent {
    private $namespace;

    public function __construct(NamespaceEntry $namespace) {
        $this->namespace = $namespace;
    }

    public function getNamespace() {
        return $this->namespace;
    }

    protected function getEventName() {
        return 'namespace.start';
    }
}
