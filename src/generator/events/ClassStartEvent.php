<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class ClassStartEvent extends AbstractEvent {
    private $class;

    public function __construct(ClassObject $class) {
        $this->class = $class;
    }

    public function getClass() {
        return $this->class;
    }

    protected function getEventName() {
        return 'class.start';
    }
}
