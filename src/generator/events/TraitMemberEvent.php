<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class TraitMemberEvent extends MemberEvent {
    private $trait;

    public function __construct(MemberObject $member, TraitObject $trait) {
        parent::__construct($member);
        $this->trait = $trait;
    }

    public function getTrait() {
        return $this->trait;
    }

    protected function getEventName() {
        return 'trait.member';
    }
}
