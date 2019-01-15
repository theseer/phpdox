<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class PHPDoxTraitsEndEvent extends AbstractEvent {
    private $traits;

    public function __construct(TraitCollection $traits) {
        $this->traits = $traits;
    }

    public function getTraits() {
        return $this->traits;
    }

    protected function getEventName() {
        return 'phpdox.traits.end';
    }
}
