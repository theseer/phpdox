<?php declare(strict_types = 1);
namespace TheSeer\phpDox\Generator;

class ClassMemberEvent extends MemberEvent {
    private $class;

    public function __construct(MemberObject $member, ClassObject $class) {
        parent::__construct($member);
        $this->class = $class;
    }

    public function getClass() {
        return $this->class;
    }

    protected function getEventName() {
        return 'class.member';
    }
}
