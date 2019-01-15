<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class ClassConstantEvent extends ConstantEvent {
    private $class;

    public function __construct(ConstantObject $constant, ClassObject $class) {
        parent::__construct($constant);
        $this->class = $class;
    }

    public function getClass() {
        return $this->class;
    }

    protected function getEventName() {
        return 'class.constant';
    }
}
