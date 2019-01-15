<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class TraitConstantEvent extends ConstantEvent {
    private $trait;

    public function __construct(ConstantObject $constant, TraitObject $trait) {
        parent::__construct($constant);
        $this->trait = $trait;
    }

    public function getTrait() {
        return $this->trait;
    }

    protected function getEventName() {
        return 'trait.constant';
    }
}
