<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class TraitMethodEvent extends MethodEvent {
    private $trait;

    public function __construct(MethodObject $method, TraitObject $trait) {
        parent::__construct($method);
        $this->trait = $trait;
    }

    public function getTrait() {
        return $this->trait;
    }

    protected function getEventName() {
        return 'trait.method';
    }
}
