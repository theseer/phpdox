<?php
namespace TheSeer\phpDox\Generator {

    abstract class MemberEvent extends AbstractEvent {

        private $member;

        public function __construct(MemberObject $member) {
            $this->member = $member;
        }

        public function getMember() {
            return $this->member;
        }

    }

}
