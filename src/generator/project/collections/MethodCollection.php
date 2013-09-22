<?php
namespace TheSeer\phpDox\Generator {

    class MethodCollection extends AbstractCollection {

        /**
         * @return MethodObject
         */
        public function current() {
            return new MethodObject($this->getCurrentNode());
        }


    }

}
