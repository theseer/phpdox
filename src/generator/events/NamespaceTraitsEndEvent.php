<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class NamespaceTraitsEndEvent extends AbstractEvent {
    private $traits;

    private $namespace;

    public function __construct(TraitCollection $traits, $namespace) {
        $this->traits    = $traits;
        $this->namespace = $namespace;
    }

    public function getTraits() {
        return $this->traits;
    }

    public function getNamespace() {
        return $this->namespace;
    }

    protected function getEventName() {
        return 'namespace.traits.end';
    }
}
