<?php
namespace TheSeer\phpDox\Generator {

    class InterfaceCollection extends AbstractCollection {

        /**
         * @return InterfaceEntry
         */
        public function current() {
            return new InterfaceEntry($this->getCurrentNode());
        }


    }

}
