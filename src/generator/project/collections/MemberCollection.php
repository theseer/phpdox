<?php
namespace TheSeer\phpDox\Generator {

    class MemberCollection extends AbstractCollection {

        /**
         * @return MemberObject
         */
        public function current() {
            return new MemberObject($this->getCurrentNode());
        }


    }

}
