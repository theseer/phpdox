<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class ClassMethodEvent extends MethodEvent {
    private $class;

    public function __construct(MethodObject $method, ClassObject $class) {
        parent::__construct($method);
        $this->class = $class;
    }

    public function getClass() {
        return $this->class;
    }

    protected function getEventName() {
        return 'class.method';
    }
}
