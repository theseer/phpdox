<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class PHPDoxClassesStartEvent extends AbstractEvent {
    private $classes;

    public function __construct(ClassCollection $classes) {
        $this->classes = $classes;
    }

    public function getClasses() {
        return $this->classes;
    }

    protected function getEventName() {
        return 'phpdox.classes.start';
    }
}
