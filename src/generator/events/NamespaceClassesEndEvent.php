<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class NamespaceClassesEndEvent extends AbstractEvent {
    private $classes;

    private $namespace;

    public function __construct(ClassCollection $classes, $namespace) {
        $this->classes   = $classes;
        $this->namespace = $namespace;
    }

    public function getClasses() {
        return $this->classes;
    }

    public function getNamespace() {
        return $this->namespace;
    }

    protected function getEventName() {
        return 'namespace.classes.end';
    }
}
