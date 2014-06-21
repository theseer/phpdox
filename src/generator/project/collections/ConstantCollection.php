<?php
namespace TheSeer\phpDox\Generator {

    class ConstantCollection extends AbstractCollection {

        /**
         * @return ConstantObject
         */
        public function current() {
            return new ConstantObject($this->getCurrentNode());
        }


    }

}
