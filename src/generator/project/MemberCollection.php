<?php
namespace TheSeer\phpDox\Generator {

    class MemberCollection extends AbstractCollection {

        public function current() {
            return new MemberObject($this->getCurrentNode());
        }


    }

}
