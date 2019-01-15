<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class TraitEndEvent extends AbstractEvent {
    private $trait;

    public function __construct(TraitObject $trait) {
        $this->trait = $trait;
    }

    public function getTrait() {
        return $this->trait;
    }

    protected function getEventName() {
        return 'trait.end';
    }
}
