<?php
namespace TheSeer\phpDox\Generator {

    class ClassObject extends AbstractUnitObject {

        /**
         * @return MemberCollection
         */
        public function getMembers() {
            return new MemberCollection($this->asDom()->query('phpdox:member'));
        }


    }

}
